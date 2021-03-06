<?php

namespace GenTux\PubSub\Tests;

use GenTux\PubSub\Exceptions\PubSubHandlerNotDefinedException;
use GenTux\PubSub\PubSubMessage;
use GenTux\PubSub\Tests\Stubs\AccountsCustomerCreatedMessage;
use GenTux\PubSub\Tests\Stubs\HandleMissingMessage;

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

    /** @test */
    public function it_throws_exception_when_handle_is_not_defined()
    {
        $this->setExpectedException(
            PubSubHandlerNotDefinedException::class,
            'Missing GenTux\PubSub\Tests\Stubs\HandleMissingMessage::handler() method definition.'
        );

        $message = new HandleMissingMessage([]);
        $message->handle();
    }

    /** @test */
    public function it_supports_isset_and_empty_for_properties()
    {
        $this->message->data = 'herp';

        $this->assertTrue(isset($this->message->data), 'isset failed on data property');
        $this->assertFalse(empty($this->message->data), 'empty failed on data property');
    }

    /** @test */
    public function it_decodes_message_data()
    {
        $message = $this->message->decode(base64_encode('{"herp": "derp"}'));

        $this->assertEquals('derp', $message->herp);
    }

    /** @test */
    public function it_encodes_message_data()
    {
        $this->message->data = new \stdClass();
        $this->message->data->herp = 'derp';

        $message = $this->message->encodedData();
        $this->assertEquals(base64_encode(json_encode($this->message->data)), $message);
    }
}
