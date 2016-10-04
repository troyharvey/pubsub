<?php

namespace GenTux\PubSub\Tests\Stubs;

use GenTux\PubSub\PubSubMessage;

class AccountsCustomerCreatedMessage extends PubSubMessage
{
    public static $routingKey = 'accounts.customer.created';
    protected $version = 'v1';
    protected $entity = 'customer';

    public function handle()
    {
        return $this->data;
    }

    public function environment()
    {
        return getenv('APP_ENV');
    }
}
