<?php

namespace GenTux\GooglePubSub;

use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class PubSubMessageSubscriber
{
    /** @var Repository */
    protected $config;

    /** @var Application */
    protected $app;


    public function __construct(Application $app, Repository $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * Handle Pub/Sub message.
     *
     * @param array $messages
     * @param Request $request
     *
     * @throws PubSubRoutingKeyException
     */
    public function handle(Request $request, array $messages)
    {
        /** @var String $routingKey */
        $routingKey = $request->get('message.attributes.routingKey');

        foreach ($messages as $messageClass) {
            if ($messageClass::handles($routingKey)) {

                /** @var PubSubMessage $message */
                $message = new $messageClass(
                    $this->app->environment(),
                    $this->config->get('queue.connections.pubsub.project')
                );

                return $message->handle(base64_decode($request->get('message.data')));
            }
        }

        throw new PubSubRoutingKeyException($routingKey);
    }
}
