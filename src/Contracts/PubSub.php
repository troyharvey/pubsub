<?php

namespace GenTux\GooglePubSub\Contracts;

use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\GooglePubSub\PubSubMessage;
use Illuminate\Http\Request;

interface PubSub
{
    /**
     * @param PubSubMessage $message
     */
    public function publish(PubSubMessage $message);

    /**
     * @param Request $request
     * @param array $messages
     * @return PubSubMessage
     * @throws PubSubRoutingKeyException
     */
    public function subscribe(Request $request, array $messages);
}
