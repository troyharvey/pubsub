<?php

namespace GenTux\GooglePubSub;


use GenTux\GooglePubSub\Exceptions\PubSubHandlerNotDefinedException;

/**
 * @property string $data
 * @property string $project
 * @property string $routingKey
 * @property string $version
 * @property string $entity
 */
abstract class PubSubMessage
{

    /** @var string data */
    protected $data;

    /** @var string project Google project slug */
    protected $project;

    /** @var string routingKey Message routing key. e.g. accounts.customer.created */
    public static $routingKey;

    /** @var string version Message version */
    protected $version = 'v1';

    /** @var string entity Noun. Entity (or Object type). e.g. customer, tuxedo, shirt, or cart-item. */
    protected $entity;

    /**
     * The Pub Sub Routing Key is a message attribute. For example, "Customer Created"
     * is a system event. The PubSub Topic is the Customer entity - `production-v1-customer`.
     * The Routing Key includes the verb - `created`, `updated`, `deleted`. For example:
     *
     *      accounts.customer.created
     *
     * @param string $routingKey
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
        return app()->environment();
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
