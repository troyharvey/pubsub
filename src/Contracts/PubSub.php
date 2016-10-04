<?php

namespace GenTux\GooglePubSub\Contracts;

use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\GooglePubSub\PubSubMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface PubSub
{
    /**
     * Publishes a message to the topic specified in the subclasses
     * PubSubMessage object.
     *
     * @param PubSubMessage $message
     *
     * @return mixed
     */
    public function publish(PubSubMessage $message);

    /**
     * Handles inbound Pub/Sub http push messages by routing
     * the message to the correct PubSubMessage handler.
     * Message handlers are defined by subclassing PubSubMessage
     * and implementing a custom handler method.
     *
     * @param Request $request  Illuminate request
     * @param array   $messages An array of PubSubMessage classes
     *
     * @return Response
     *
     * @throws PubSubRoutingKeyException
     */
    public function subscribe(Request $request, array $messages);
}
