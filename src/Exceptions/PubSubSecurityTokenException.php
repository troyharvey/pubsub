<?php

namespace GenTux\PubSub\Exceptions;

class PubSubSecurityTokenException extends \Exception
{
    /**
     * Create a new exception for missing token.
     *
     * @param string $token
     * @return static
     */
    public static function forThis($token)
    {
        $message = implode(
            "\n",
            [
                "1) Check the GOOGLE_PUB_SUB_SUBSCRIBER_TOKEN environment variable.",
                "2) Verify the 'Push Endpoint URL' in Google Pub/Sub has a token querystring parameter set.",
                "Subscriptions: https://console.cloud.google.com/cloudpubsub/topicList",
                "Invalid token: {$token}",
            ]
        );

        return new static($message);
    }
}
