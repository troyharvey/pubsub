<?php

namespace GenTux\PubSub\Exceptions;

class PubSubHandlerNotDefinedException extends PubSubException
{
    public static function forMessage($class)
    {
        return new static("Missing {$class}::handler() method definition.");
    }
}
