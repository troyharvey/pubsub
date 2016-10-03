<?php

namespace GenTux\GooglePubSub\Tests\Drivers\Google;

use GenTux\GooglePubSub\Drivers\Google\PubSub;
use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\GooglePubSub\PubSubMessage;
use GenTux\GooglePubSub\Tests\Stubs\AccountsCustomerCreatedMessage;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Mockery;

class PubSubTest extends \PHPUnit_Framework_TestCase
{
    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    /** @var PubSub */
    protected $client;

    public function setUp()
    {
        $this->app = Mockery::mock(Application::class);
        $this->config = Mockery::mock(Repository::class);

        $this->client = new PubSub($this->app, $this->config);
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
            \GenTux\GooglePubSub\Contracts\PubSub::class,
            $this->client,
            'The Google PubSub client does not implement the PubSub interface.'
        );
    }
    
    /** @test */
    public function it_publishes_a_message()
    {
        $googleClient = Mockery::mock('overload:Google_Client');
        $googleClient->shouldReceive('useApplicationDefaultCredentials')
            ->shouldReceive('setScopes')
            ->with(['https://www.googleapis.com/auth/pubsub'])
            ->once();

        Mockery::mock('overload:Google_Service_Pubsub_Resource_ProjectsTopics')
            ->shouldReceive('publish')
            ->once();

        $this->config
            ->shouldReceive('get')
            ->with('pubsub.project')
            ->once()
            ->andReturn('google-project-id');

        $message = new AccountsCustomerCreatedMessage([
            'firstName' => 'G.K.',
            'lastName' => 'Testerton',
        ]);

        $this->client->publish($message);
    }

    /** @test */
    public function it_subscribes_to_a_message()
    {
        $request = new Request();
        $request->merge(
            [
                'message' => [
                    'data' => base64_encode(json_encode(['herpy' => 'derpy'])),
                    'attributes' => [
                        'routingKey' => 'accounts.customer.created',
                    ],
                    'message_id' => uniqid(),
                ]
            ]
        );

        /** @var PubSubMessage $message */
        $message = $this->client->subscribe(
            $request,
            [
                AccountsCustomerCreatedMessage::class
            ]
        );
        
        $this->assertInstanceOf(AccountsCustomerCreatedMessage::class, $message);

        $result = $message->handle($request);
        $this->assertEquals('{"herpy":"derpy"}', $result);
    }

    /** @test */
    public function it_throws_routing_key_exception_for_invalid_messages()
    {
        $request = new Request();
        $request->merge(
            [
                'message' => [
                    'attributes' => [
                        'routingKey' => 'herpy.derpy.derp',
                    ],
                ]
            ]
        );

        $this->setExpectedException(PubSubRoutingKeyException::class);
        
        /** @var PubSubMessage $message */
        $this->client->subscribe(
            $request,
            [
                AccountsCustomerCreatedMessage::class
            ]
        );
    }
}
