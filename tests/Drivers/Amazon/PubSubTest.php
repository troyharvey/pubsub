<?php

namespace GenTux\PubSub\Tests\Drivers\Amazon;

use GenTux\PubSub\Drivers\Amazon\PubSub;
use GenTux\PubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\PubSub\Exceptions\PubSubTopicNotDefinedException;
use GenTux\PubSub\Tests\Stubs\AccountsCustomerCreatedMessage;
use GuzzleHttp\Client;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;

class PubSubTest extends \PHPUnit_Framework_TestCase
{
    /** @var Application|Mockery\MockInterface */
    protected $app;

    /** @var Repository|Mockery\MockInterface */
    protected $config;

    /** @var Log|Mockery\MockInterface */
    protected $log;

    /** @var ResponseFactory|Mockery\MockInterface */
    protected $response;

    /** @var PubSub */
    protected $driver;

    /** @var Client */
    protected $guzzle;

    public function setUp()
    {
        parent::setUp();

        $this->app = Mockery::mock(Application::class);
        $this->config = Mockery::mock(Repository::class);
        $this->response = Mockery::mock(ResponseFactory::class);
        $this->log = Mockery::mock(Log::class);
        $this->guzzle = Mockery::mock(Client::class);
        $this->driver = new PubSub(
            $this->app,
            $this->config
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        Mockery::close();
    }

    /** @test */
    public function it_implements_pub_sub_interface()
    {
        $this->assertInstanceOf(
            \GenTux\PubSub\Contracts\PubSub::class,
            $this->driver,
            'The Amazon PubSub driver does not implement the PubSub interface.'
        );
    }

    /** @test */
    public function it_publishes_a_message()
    {
        Mockery::mock('overload:AmazonSNS')
            ->shouldReceive('listTopics')
            ->once()
            ->andReturn(
                [
                    ['TopicArn' => 'arn:aws:sns:us-east-1:913295962036:testing-v1-partner'],
                    ['TopicArn' => 'arn:aws:sns:us-east-1:913295962036:testing-v1-customer'],
                    ['TopicArn' => 'arn:aws:sns:us-east-1:913295962036:testing-v1-payments'],
                ]
            )
            ->shouldReceive('publish')
            ->once()
            ->andReturn('MESSAGEID');

        $this->config
            ->shouldReceive('get')
            ->once()
            ->with('pubsub.amazon.accessKeyId')
            ->andReturn('herpyderpy')
            ->shouldReceive('get')
            ->once()
            ->with('pubsub.amazon.secretAccessKey')
            ->andReturn('hurdygurdy');

        $message = new AccountsCustomerCreatedMessage([
            'firstName' => 'G.K.',
            'lastName' => 'Testerton',
        ]);

        $this->assertEquals('MESSAGEID', $this->driver->publish($message));
    }

    /** @test */
    public function it_subscribes()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('getContent')
            ->once()
            ->andReturn($this->messageBody('accounts.customer.created'));

        /** @var Response $actualResponse */
        $actualResponse = $this->driver->subscribe(
            $request,
            [
                AccountsCustomerCreatedMessage::class
            ]
        );

        $this->assertEquals(204, $actualResponse->getStatusCode());
    }

    /** @test */
    public function it_throws_routing_key_exception_for_unhandled_messages()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('getContent')
            ->twice()
            ->andReturn($this->messageBody('some.bogus.routing.key'));

        $this->setExpectedException(PubSubRoutingKeyException::class);

        /** @var Response $actualResponse */
        $this->driver->subscribe(
            $request,
            [
                AccountsCustomerCreatedMessage::class
            ]
        );
    }
    
    /** @test */
    public function it_handles_confirm_subscription_sns_message()
    {
        $url = 'https://sns.amazon.com/topics/913295962036:qa-v1-partner/subscribe';

        $this->guzzle
            ->shouldReceive('get')
            ->with($url);

        $this->app
            ->shouldReceive('make')
            ->with('GuzzleHttp\Client')
            ->andReturn($this->guzzle);

        $message = new \stdClass();
        $message->Type = 'SubscriptionConfirmation';
        $message->SubscribeURL = $url;

        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('getContent')
            ->once()
            ->andReturn(json_encode($message));

        /** @var Response $actualResponse */
        $actualResponse = $this->driver->subscribe($request, []);

        $this->assertEquals(204, $actualResponse->getStatusCode());
    }

    /** @test */
    public function it_returns_false_when_message_is_not_confirm_subscription_type()
    {
        $message = new \stdClass();
        $message->Type = 'YouAintGottaTrySoHard';

        $this->assertFalse($this->driver->confirmingSubscription($message));
    }
    
    /** @test */
    public function it_finds_topic_from_a_list_of_sns_topics()
    {
        $topics = [
            ['TopicArn' => 'arn:aws:sns:us-east-1:913295962036:qa-v1-partner'],
            ['TopicArn' => 'arn:aws:sns:us-east-1:913295962036:qa-v1-customer'],
            ['TopicArn' => 'arn:aws:sns:us-east-1:913295962036:qa-v1-payments'],
        ];
        
        $this->assertEquals(
            'arn:aws:sns:us-east-1:913295962036:qa-v1-partner',
            $this->driver->findTopic($topics, 'qa-v1-partner')
        );
    }

    /** @test */
    public function it_throws_exception_when_topic_not_found_in_list()
    {
        $topics = [];

        $this->setExpectedException(PubSubTopicNotDefinedException::class);

        $this->assertEquals(
            'arn:aws:sns:us-east-1:913295962036:qa-v1-partner',
            $this->driver->findTopic($topics, 'qa-v1-partner')
        );
    }

    public function messageBody($routingKey)
    {
        return <<<EOT
{
  "Type" : "Notification",
  "MessageId" : "e9474356-5a41-5b63-9da4-13ac1af8f174",
  "TopicArn" : "arn:aws:sns:us-east-1:913295962036:local-v1-partner",
  "Subject" : "{$routingKey}",
  "Message" : "eyJwYXJ0bmVyX2lkIjoiSE9XRFlET09EWSIsImlkIjoiZ1JnbjJQUThhWENsR1NFWmtpN3o4RFhLMFppNm03amgifQ==",
  "Timestamp" : "2017-01-19T04:20:09.357Z",
  "SignatureVersion" : "1",
  "Signature" : "Wol6ysFw9e6MDPAFURTIOy7NXwFB9v544WVZA38+WqWu5fhRBeKfLGJBxSRrCxWaZNqXLCl7pLduq8+/XhcAFDFx7ToIbkkQ+Vkkijj1VuOsgLbSZ5RqzeCwRxxL4jo9NQJBxvR0RCXOZC/ePeoePdbQImMeKXQpoXqkb6Gk39d1xoDdURiDdIz4CBn+Kp9mnbhNGA+jpklrUHNkVy0JHpeylIdd3zwg3DWBcTsKi3/QvWeMx2pr73w5Ntexlt0nInevuioI4AKHJumtAsWxQxWu5rlqVppHrfvbRyur4ZwhWi0qv3anNGdqhP130BJAQaRPcxDjd9nf8MKj9U/Klw==",
  "SigningCertURL" : "https://sns.us-east-1.amazonaws.com/SimpleNotificationService-b95095beb82e8f6a046b3aafc7f4149a.pem",
  "UnsubscribeURL" : "https://sns.us-east-1.amazonaws.com/?Action=Unsubscribe&SubscriptionArn=arn:aws:sns:us-east-1:913295962036:local-v1-partner:debbf13a-8cb4-46cf-bc1b-a0d4d3c98e48"
}
EOT;
    }
}
