<?php

namespace GenTux\PubSub\Tests;

use GenTux\PubSub\PubSubMessage;
use GenTux\PubSub\Tests\Stubs\AccountsCustomerCreatedMessage;

class PubSubMessageTest extends \PHPUnit_Framework_TestCase
{
    /** @var PubSubMessage */
    protected $message;

    public function setUp()
    {
        $this->message = new AccountsCustomerCreatedMessage('production', 'acme-anvil');
    }

    /** @test */
    public function it_statically_handles_a_routing_key()
    {
        $this->assertTrue(AccountsCustomerCreatedMessage::handles('accounts.customer.created'));
        $this->assertFalse(AccountsCustomerCreatedMessage::handles('accounts.customer.deleted'));
    }

    /** @test */
    public function it_has_a_topic()
    {
        $this->assertEquals('testing-v1-customer', $this->message->topic());
    }

    /** @test */
    public function it_wraps_environment_in_a_method()
    {
        $this->assertEquals('testing', $this->message->environment());
    }

    /** @test */
    public function it_implements_handle_method()
    {
        $this->message->data = json_decode('{"herp": "derp"}');

        $this->assertEquals($this->message->data, $this->message->handle());
    }
}
