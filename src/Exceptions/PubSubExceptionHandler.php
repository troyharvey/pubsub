<?php

namespace GenTux\PubSub\Exceptions;

trait PubSubExceptionHandler
{
    /**
     * Render PubSub exception.
     *
     * @param PubSubException $e
     *
     * @return \Illuminate\Http\Response
     */
    public function handlePubSubException(PubSubException $e)
    {
        if ($e instanceof PubSubSecurityTokenException) {
            /**
             * Invalid security token. Don't retry the message.
             */
            return response(200);
        }
    }
}
