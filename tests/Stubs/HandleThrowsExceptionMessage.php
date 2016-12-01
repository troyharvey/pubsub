<?php

namespace GenTux\PubSub\Tests\Stubs;

use GenTux\PubSub\Exceptions\PubSubHandlerNotDefinedException;
use GenTux\PubSub\PubSubMessage;

class HandleThrowsExceptionMessage extends PubSubMessage
{
    public static $routingKey = 'accounts.customer.throw.exception';
    protected $version = 'v1';
    protected $entity = 'customer';

    public function handle()
    {
        throw new PubSubHandlerNotDefinedException('Herp derper.');
    }
}
