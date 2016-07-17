<?php

namespace GenTux\GooglePubSub;

use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use Illuminate\Http\Request;

class PubSubMessageFactory
{
    /** @var array */
    protected $messages;

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @param Request $request
     * @throws PubSubRoutingKeyException
     */
    public function make(Request $request)
    {
        /** @var String $routingKey */
        $routingKey = $request->input('message.attributes.routingKey');

        foreach ($this->messages as $messageClass) {
            if (strcasecmp($messageClass::routingKey(), $routingKey) == 0) {
                /** @var PubSubMessage $message */
                $message = new $messageClass(
                    app()->environment(),
                    config('queue.connections.pubsub.app'),
                    config('queue.connections.pubsub.project')
                );
                return $message->handle($request);
            }
        }

        throw new PubSubRoutingKeyException($routingKey);
    }
}
