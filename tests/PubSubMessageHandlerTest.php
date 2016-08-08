<?php

namespace GenTux\GooglePubSub\Tests;

use GenTux\GooglePubSub\PubSubMessageSubscriber;
use GenTux\GooglePubSub\Tests\Stubs\AccountsCustomerCreatedMessage;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Mockery;

class PubSubMessageHandlerTest extends \PHPUnit_Framework_TestCase
{

    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    /** @var PubSubMessageSubscriber */
    protected $messageHandler;

    public function setUp()
    {
        $this->app = Mockery::mock(Application::class);
        $this->app
            ->shouldReceive('environment')
            ->andReturn('testing');
        
        $this->config = Mockery::mock(Repository::class);
        $this->config
            ->shouldReceive('get')
            ->with('queue.connections.pubsub.project')
            ->andReturn('pubsub-demo');

        $this->messageHandler = new PubSubMessageSubscriber($this->app, $this->config);
    }


    /** @test */
    public function it_handles_an_inbound_message() 
    {
        $customer = new \stdClass();
        $customer->firstName = 'G.K.';
        $customer->lastName = 'Testerton';
        $customer->email = 'gktesterton@gmail.com';

        $request = new Request();
        $request->request->add(
            [
                'message.data' => base64_encode(json_encode($customer)),
                'message.attributes.routingKey' => 'accounts.customer.created',
                'message.message_id' => uniqid()
            ]
        );
        
        $this->messageHandler->handle(
            $request,
            [
                AccountsCustomerCreatedMessage::class,
            ]
        );
    }
}
