<?php

namespace GenTux\PubSub\Drivers\Google;

use GenTux\PubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\PubSub\PubSubMessage;
use Google_Client;
use Google_Service_Pubsub;
use Google_Service_Pubsub_PublishRequest;
use Google_Service_Pubsub_PubsubMessage;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;


class PubSub implements \GenTux\PubSub\Contracts\PubSub
{
    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    public function __construct(
        Application $app,
        Repository $config
    ) {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * Publish a message using Google PubSub.
     *
     * @param PubSubMessage $message
     *
     * @return mixed
     */
    public function publish(PubSubMessage $message)
    {

        /** @var Google_Service_Pubsub_PublishRequest $request */
        $request = new Google_Service_Pubsub_PublishRequest();

        /** @var Google_Service_Pubsub_PubsubMessage $pubSubMessage */
        $pubSubMessage = new Google_Service_Pubsub_PubsubMessage();

        /** @var Google_Client $client */
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes([Google_Service_Pubsub::PUBSUB]);

        /** @var Google_Service_Pubsub $pubsub */
        $pubsub = new Google_Service_Pubsub($client);

        $pubSubMessage->setData(
            base64_encode(
                $message->encode($message->data)
            )
        );

        $pubSubMessage->setAttributes(
            [
                'routingKey' => $message::$routingKey
            ]
        );

        $request->setMessages([$pubSubMessage]);

        return $pubsub->projects_topics->publish(
            "projects/{$this->config->get('pubsub.project')}/topics/{$message->topic()}",
            $request
        );
    }


    /**
     * Handles an HTTP POST message pushed by Google PubSub.
     * Finds and returns a PubSubMessage by matching up the `routingKey`
     * attribute of the push message with class that handles that
     * `routingKey`.
     *
     * @param Request $request  Request
     * @param array   $messages Messages
     *
     * @throws PubSubRoutingKeyException
     *
     * @return PubSubMessage
     */
    public function subscribe(Request $request, array $messages)
    {
        $routingKey = $request->input('message.attributes.routingKey');

        foreach ($messages as $messageClass) {
            if ($messageClass::handles($routingKey)) {

                /**
                 * Message data must be base64-encoded, and can be a maximum
                 * of 10MB after encoding.
                 *
                 * @var string $messageData
                 */
                $messageData = base64_decode($request->input('message.data'));
                $messageData = $messageClass::decode($messageData);

                /** @var PubSubMessage $message */
                $message = new $messageClass($messageData);
                $message->handle();

                return $this->app->make('Response')->make('', 204);
            }
        }

        // Oops. None of the $messages handle this Routing Key.
        throw PubSubRoutingKeyException::forThis($request);
    }
}
