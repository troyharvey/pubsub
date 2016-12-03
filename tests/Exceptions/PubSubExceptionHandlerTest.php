<?php

namespace GenTux\PubSub\Exceptions;

use Illuminate\Http\Response;

class PubSubExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    use PubSubExceptionHandler;

    /** @test */
    public function it_handles_security_token_exception()
    {
        /** @var PubSubSecurityTokenException $e */
        $e = PubSubSecurityTokenException::forThis('TERRIBLE_SECURITY_TOKEN');

        $expectedResponse = new Response('', 200);

        $this->assertEquals(
            $expectedResponse,
            $this->handlePubSubException($e)
        );
    }
}
