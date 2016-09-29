<?php

namespace GenTux\GooglePubSub\Drivers\Google;


use GenTux\GooglePubSub\PubSubMessage;
use Google_Client;
use Google_Service_Pubsub;
use Google_Service_Pubsub_PublishRequest;
use Google_Service_Pubsub_PubsubMessage;
use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class PubSub implements \GenTux\GooglePubSub\Contracts\PubSub
{
    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    public function __construct(Application $application, Repository $config)
    {
        $this->app = $application;
        $this->config = $config;
    }

    public function publish(PubSubMessage $message)
    {
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes([Google_Service_Pubsub::PUBSUB]);

        /** @var Google_Service_Pubsub pubsub */
        $pubsub = new Google_Service_Pubsub($client);

        /** @var Google_Service_Pubsub_PubsubMessage $pubSubMessage */
        $pubSubMessage = new Google_Service_Pubsub_PubsubMessage();

        $json = json_encode($message->data);
        $pubSubMessage->setData(base64_encode($json));
        $pubSubMessage->setAttributes([
            'routingKey' => $message::$routingKey
        ]);

        /** @var Google_Service_Pubsub_PublishRequest $request */
        $request = new Google_Service_Pubsub_PublishRequest();
        $request->setMessages([$pubSubMessage]);

        $pubsub->projects_topics->publish(
            "projects/{$this->config->get('queue.connections.pubsub.project')}/topics/{$message->topic()}",
            $request
        );
    }


    public function subscribe(Request $request, array $messages)
    {
        /** @var String $routingKey */
        $routingKey = $request->input('message.attributes.routingKey');

        foreach ($messages as $messageClass) {
            if ($messageClass::handles($routingKey)) {

                /** @var PubSubMessage $message */
                return new $messageClass(
                    base64_decode($request->input('message.data'))
                );
            }
        }

        throw PubSubRoutingKeyException::forThis($request);
    }
}
