<?php

namespace GenTux\PubSub\Exceptions;

class PubSubTopicNotDefinedException extends PubSubException
{
    /**
     * No matching PubSub topic was found.
     *
     * @param $topic
     *
     * @return static
     */
    public static function forTopic($topic)
    {
        return new static($topic);
    }
}
