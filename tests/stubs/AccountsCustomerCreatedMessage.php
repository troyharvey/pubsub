<?php

namespace GenTux\GooglePubSub\Tests\Stubs;

use GenTux\GooglePubSub\PubSubMessage;

class AccountsCustomerCreatedMessage extends PubSubMessage
{
    protected static $routingKey = 'accounts.customer.created';
    protected $version = 'v1';
    protected $entity = 'customer';
    protected $event = 'created';
}
