<?php

namespace GenTux\PubSub\Exceptions;

use Illuminate\Http\Request;

class PubSubRoutingKeyException extends PubSubException
{
    /**
     * Create a new exception for unhandled routing key.
     *
     * @param Request $request
     * @return static
     */
    public static function forThis(Request $request)
    {
        $message = implode(
            "\n",
            [
                "Request: {$request->getContent()}",
                "\n",
                "1) Verify that a Pub Sub Message handler class exists for this routing key.",
                "2) The subscriber endpoint that calls PubSub->subscribe() must include an " .
                "array of all the handled Pub Sub Messages.",
            ]
        );

        return new static($message);
    }
}
