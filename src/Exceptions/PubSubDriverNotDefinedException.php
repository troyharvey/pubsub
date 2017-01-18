<?php

namespace GenTux\PubSub\Exceptions;

class PubSubDriverNotDefinedException extends PubSubException
{
    public function __construct()
    {
        $message = implode(
            "\n",
            [
                "Create a config file - \\config\\pubsub.php and add a 'driver' key.",
                "Valid driver settings:",
                "\n",
                "1) google",
                "2) amazon",
                "3) sync",
            ]
        );

        parent::__construct($message);
    }
}
