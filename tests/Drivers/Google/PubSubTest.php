<?php

namespace GenTux\PubSub\Tests\Drivers\Google;

use GenTux\PubSub\Drivers\Google\PubSub;
use GenTux\PubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\PubSub\PubSubMessage;
use GenTux\PubSub\Tests\Stubs\AccountsCustomerCreatedMessage;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Writer;
use Mockery;

class PubSubTest extends \PHPUnit_Framework_TestCase
{
    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    /** @var Writer */
    protected $log;

    /** @var ResponseFactory */
    protected $response;

    /** @var PubSub */
    protected $client;

    public function setUp()
    {
        $this->app = Mockery::mock(Application::class);
        $this->config = Mockery::mock(Repository::class);
        $this->log = Mockery::mock(Writer::class);
        $this->response = Mockery::mock(ResponseFactory::class);

        $this->client = new PubSub(
            $this->app,
            $this->config,
            $this->log,
            $this->response
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

        $noContentResponse = new Response();
        $noContentResponse->setStatusCode(204);

        $this->response
            ->shouldReceive('make')
            ->with("", 204)
            ->andReturn($noContentResponse);


        /** @var PubSubMessage $message */
        $actualResponse = $this->client->subscribe(
            $request,
            [
                AccountsCustomerCreatedMessage::class
            ]
        );

        $this->assertEquals($noContentResponse, $actualResponse);
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
