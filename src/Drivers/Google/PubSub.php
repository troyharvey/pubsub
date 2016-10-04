<?php

namespace GenTux\GooglePubSub\Drivers\Google;

use GenTux\GooglePubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\GooglePubSub\PubSubMessage;
use Google_Client;
use Google_Service_Pubsub;
use Google_Service_Pubsub_PublishRequest;
use Google_Service_Pubsub_PubsubMessage;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Log\Writer;

class PubSub implements \GenTux\GooglePubSub\Contracts\PubSub
{
    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    /** @var Writer */
    protected $log;

    /** @var ResponseFactory */
    protected $response;

    public function __construct(
        Application $app,
        Repository $config,
        Writer $log,
        ResponseFactory $response
    ) {
        $this->app = $app;
        $this->config = $config;
        $this->log = $log;
        $this->response = $response;
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
     * @param Request $request
     * @param array $messages
     *
     * @throws PubSubRoutingKeyException
     *
     * @return PubSubMessage
     */
    public function subscribe(Request $request, array $messages)
    {
        /** @var String $routingKey */
        $routingKey = $request->input('message.attributes.routingKey');

        foreach ($messages as $messageClass) {
            if ($messageClass::handles($routingKey)) {

                /** @var PubSubMessage $message */
                $message = new $messageClass(
                    base64_decode($request->input('message.data'))
                );

                try {
                    $message->handle();
                } catch (\Exception $e) {
                    $this->log->error(
                        implode(
                            "\n",
                            [
                                get_class($e),
                                $e->getMessage(),
                                $e->getCode(),
                                json_encode($request->all()),
                            ]
                        )
                    );
                } finally {
                    return $this->response->make('', 204);
                }
            }
        }

        throw PubSubRoutingKeyException::forThis($request);
    }
}
