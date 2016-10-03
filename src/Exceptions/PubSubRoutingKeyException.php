<?php

namespace GenTux\GooglePubSub\Exceptions;

use Illuminate\Http\Request;

class PubSubRoutingKeyException extends \Exception
{
    /**
     * Create a new exception for unhandled routing key.
     *
     * @param Request $request
     * @return static
     */
    public static function forThis(Request $request)
    {
        $input = json_encode($request->all());

        $message = implode(
            "\n",
            [
                "Unhandled Routing Key: {$request->input('message.attributes.routingKey')}",
                "Request: {$input}",
                "\n",
                "1) Verify that a Pub Sub Message class exists for this routing key.",
                "2) The subscriber endpoint that calls PubSub->subscribe() must include an " .
                "array of all the handled Pub Sub Messages.",
            ]
        );

        return new static($message);
    }
}
