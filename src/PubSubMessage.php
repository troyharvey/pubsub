<?php

namespace GenTux\PubSub;

use GenTux\PubSub\Exceptions\PubSubHandlerNotDefinedException;

/**
 * @property string $data
 * @property string $project
 * @property static string $routingKey
 * @property string $version
 * @property string $entity
 */
abstract class PubSubMessage
{

    /** @var \stdClass data */
    protected $data;

    /** @var string routingKey Message routing key. e.g. accounts.customer.created */
    public static $routingKey;

    /** @var string version Message version */
    protected $version = 'v1';

    /**
     * @var string entity Noun. Entity (or Object type). e.g. customer,
     * tuxedo, shirt, or cart-item.
     */
    protected $entity;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * JSON encode the payload of every message by default. Google requires
     * the `message.data` property to be Base64 encoded for all messages.
     * What you decide to Base64 encode is up to you. We think it should be
     * JSON by default.
     *
     * Override according to taste.
     *
     * @return string
     */
    public function encodedData()
    {
        return base64_encode(
            json_encode($this->data)
        );
    }

    /**
     * Prior to instantiating a Message, subscribers decode the message
     * payload in the `message.data` property. Typically, the message data
     * attribute is JSON that is Base64 encoded. So, by default this decode
     * method just decodes the JSON.
     *
     * Override to taste.
     *
     * @param String $data Message data
     *
     * @return \stdClass
     */
    public static function decode($data)
    {
        return json_decode(
            base64_decode($data)
        );
    }

    /**
     * The Pub Sub Routing Key is a message attribute. For example,
     * "Customer Created" is a system event. The PubSub Topic is the Customer
     * entity - `production-v1-customer`. The Routing Key includes the verb -
     * `created`, `updated`, `deleted`. For example:
     *
     *      `accounts.customer.created`
     *
     * @param string $routingKey Routing key
     *
     * @return bool
     */
    public static function handles($routingKey)
    {
        return strcasecmp(static::$routingKey, $routingKey) == 0;
    }

    /**
     * Handle inbound PubSub message.
     *
     * @throws PubSubHandlerNotDefinedException
     *
     * @return string
     */
    public function handle()
    {
        throw PubSubHandlerNotDefinedException::forMessage(get_class($this));
    }

    /**
     * PubSub message Topic name following this convention:
     *
     *   {environment}-{version}-{entity}
     *
     * Override for customized topic names.
     *
     * @return string
     */
    public function topic()
    {
        $nubs = [];

        if (!empty($this->environment())) {
            $nubs[] = $this->environment();
        }

        if (!empty($this->version)) {
            $nubs[] = $this->version;
        }

        if (!empty($this->entity)) {
            $nubs[] = $this->entity;
        }

        return join('-', $nubs);
    }

    /**
     * String representing the current environment.
     *
     * @return string
     */
    public function environment()
    {
        return getenv('APP_ENV');
    }

    /**
     * @param $name
     * @return null | mixed
     */
    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * @param $property
     * @param $value
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
    /**
     * @param $property
     * @return bool
     */
    public function __isset($property)
    {
        return property_exists($this, $property) && $this->$property != null;
    }
}
