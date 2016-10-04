<?php

namespace GenTux\GooglePubSub\Tests\Http;

use GenTux\GooglePubSub\Exceptions\PubSubSecurityTokenException;
use GenTux\GooglePubSub\Http\PubSubMiddleware;
use Illuminate\Http\Request;
use Mockery;

class PubSubMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_validates_pub_sub_security_token()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $middleware = new PubSubMiddleware();

        $request->shouldReceive('input')
            ->with('token')
            ->andReturn('7RWfH4zxnXXsep5g3LpVxv7xSlnhyFPFeHda87i3Vc');

        $result = $middleware->handle(
            $request,
            function () {
            }
        );
        $this->assertEquals(null, $result);
    }


    /** @test */
    public function it_throws_invalid_pub_sub_security_token_exception()
    {
        /** @var Request $request */
        $request = Mockery::mock(Request::class);
        $middleware = new PubSubMiddleware();

        $request->shouldReceive('input')
            ->with('token')
            ->andReturn('herpderp');

        $message = implode(
            "\n",
            [
                "1) Check the GOOGLE_PUB_SUB_SUBSCRIBER_TOKEN environment variable.",
                "2) Verify the 'Push Endpoint URL' in Google Pub/Sub has a token querystring parameter set.",
                "Subscriptions: https://console.cloud.google.com/cloudpubsub/topicList",
                "Invalid token: herpderp",
            ]
        );

        $this->setExpectedException(PubSubSecurityTokenException::class, $message);
        $result = $middleware->handle(
            $request,
            function () {
                return true;
            }
        );
        $this->assertTrue($result);
    }
}
