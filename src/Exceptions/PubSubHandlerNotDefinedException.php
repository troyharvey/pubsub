<?php

namespace GenTux\PubSub\Exceptions;

class PubSubHandlerNotDefinedException extends \Exception
{
    public static function forMessage($class)
    {
        return new static("Missing {$class}::handler() method definition.");
    }
}
