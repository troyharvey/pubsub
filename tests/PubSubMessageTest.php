<?php

namespace GenTux\GooglePubSub\Tests;

use GenTux\GooglePubSub\PubSubMessage;
use GenTux\GooglePubSub\Tests\Stubs\AccountsCustomerCreatedMessage;

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
}
