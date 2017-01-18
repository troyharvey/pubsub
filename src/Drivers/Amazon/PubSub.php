<?php

namespace GenTux\PubSub\Drivers\Amazon;

use AmazonSNS;
use GenTux\PubSub\Exceptions\PubSubRoutingKeyException;
use GenTux\PubSub\Exceptions\PubSubTopicNotDefinedException;
use GenTux\PubSub\PubSubMessage;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PubSub implements \GenTux\PubSub\Contracts\PubSub
{
    const SUBSCRIPTION_CONFIRMATION = 'SubscriptionConfirmation';

    /** @var Application */
    protected $app;

    /** @var Repository */
    protected $config;

    public function __construct(Application $app, Repository $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function publish(PubSubMessage $message)
    {
        $sns = new AmazonSNS(
            $this->config->get('pubsub.amazon.accessKeyId'),
            $this->config->get('pubsub.amazon.secretAccessKey')
        );

        $arn = $this->findTopic($sns, $message->topic());

        $sns->publish(
            $arn,
            $message->encodedData(),
            $message::$routingKey
        );
    }

    /**
     * @inheritdoc
     */
    public function subscribe(Request $request, array $messages)
    {
        $message = json_decode($request->getContent());

        /**
         * The first request to this endpoint will be a Confirm
         * Subscription message.
         */
        if ($this->confirmingSubscription($message)) {
            return new Response('', 204);
        }

        foreach ($messages as $messageClass) {
            if ($messageClass::handles($message->Subject)) {
                /** @var PubSubMessage $message */
                $message = new $messageClass(
                    $messageClass::decode($message->Message)
                );

                $message->handle();

                return new Response('', 204);
            }
        }

        throw PubSubRoutingKeyException::forThis($request);
    }

    /**
     * Confirm Amazon SNS topic subscription. When a new subscriber
     * is added, a confirmation message is sent once with a `SubscribeURL`.
     * This method will confirm the subscription to the topic by sending
     * a GET request to the `SubscribeURL`.
     *
     * @param \stdClass $message Amazon SNS HTTP message body.
     *
     * @return bool
     */
    protected function confirmingSubscription($message)
    {
        if ($message->Type == self::SUBSCRIPTION_CONFIRMATION) {
            $this->app
                ->make(\GuzzleHttp\Client::class)
                ->get($message->SubscribeURL);

            return true;
        }

        return false;
    }

    /**
     * Find the full Amazon Resource Name (ARN) for a topic
     * in a list of all topics.
     *
     * @param AmazonSNS $sns   SNS client
     * @param string    $topic Suffix of the Topic ARN
     *
     * @return string Amazon Topic ARN
     *
     * @throws PubSubTopicNotDefinedException
     */
    protected function findTopic(AmazonSNS $sns, $topic)
    {
        foreach ($sns->listTopics() as $arn) {
            if (stripos($arn['TopicArn'], $topic)) {
                return $arn['TopicArn'];
            }
        }

        throw PubSubTopicNotDefinedException::forTopic($topic);
    }
}
