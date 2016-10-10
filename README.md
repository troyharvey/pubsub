# PubSub

[![Latest Version](https://img.shields.io/github/release/generationtux/pubsub.svg?style=flat-square)](https://github.com/generationtux/pubsub/releases)
[![Coverage Status](https://img.shields.io/codecov/c/github/generationtux/pubsub.svg?maxAge=2592000?style=flat-square)](https://codecov.io/gh/generationtux/pubsub/)
[![Build Status](https://img.shields.io/travis/generationtux/pubsub/master.svg?style=flat-square)](https://travis-ci.org/generationtux/pubsub)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

PubSub is a push notification system for your Lumen (and Laravel) backend systems. `Publishers` add
`Messages` to a `Topic` and `Subscribers` receive the message as a push
notification. Think of it as
[Laravel Echo](https://laravel.com/docs/5.3/broadcasting) for microservices.

![](https://cloud.google.com/pubsub/images/pub_sub_flow.svg)

## Highlights

* Build loosely coupled, scalable systems consistent with your other
message-oriented Laravel code
* Simple API that leaves your Laravel code looking clean
* Driver-based so changing Pub/Sub providers is a simple configuration change
* Delete the duplicated Pub/Sub boilerplate in each of your apps
* [Google Pub/Sub](https://cloud.google.com/pubsub/docs/overview) support
* [Amazon SNS](https://aws.amazon.com/sns/) support __coming soon__

## Install

1. Install `PubSub` using [Composer](https://getcomposer.org/).

```bash
composer require generationtux/pubsub
```

2. Add the service provider to `config/app.php`:

```php
/*
 * Package Service Providers...
 */

GenTux\PubSub\PubSubServiceProvider::class,
```

## Pub/Sub Messages

Now you can get started defining your first Pub/Sub message. Every message must have 3 properties:

1. `$routingKey` is a unique key within your application. It should follow this convention: Name of the app publishing the message dot Entity name dot Event name. Routing keys allow you to have a single topic per Object type. For example, "Customer" `created`, `updated`, and `deleted` messages can all be published to the same topic and the subscribers use the routing key to handle each message type accordingly.
2. `$version` is used to version Topics.
3. `$entity` is name of the object type (entity) that the message describes.

This is a "Customer Created" message definition.

```php
<?php

namespace App\PubSub;

class CustomerCreatedMessage extends GenTux\PubSub\PubSubMessage
{
    public static $routingKey = 'accounts.customer.created';
    protected $version = 'v1';
    protected $entity = 'customer';
}
```

## Google Pub/Sub

To begin using the [Google Pub/Sub driver](https://cloud.google.com/pubsub/docs/overview) you will need to create a Google Cloud project and a Pub/Sub topic.

1. Visit [console.cloud.google.com](https://console.cloud.google.com) and create a [Google Cloud Project](http://i.imgur.com/HQZMJCH.gifv).
2. Add the [Google Project Id](http://i.imgur.com/h5NJTBP.png) to your `.env` file.

        GOOGLE_PUBSUB_PROJECT_ID=pub-sub-demo-145921

3. [Create a Pub/Sub Topic](http://i.imgur.com/KXRLhVA.gifv) under :fa-navicon: > Big Data > [Pub/Sub](https://console.cloud.google.com/cloudpubsub/topicList). Use the `environment`-`version`-`entity` naming convention or override the [topic](/generationtux/pubsub/blob/master/src/PubSubMessage.php) method in your `Message`.

        local-v1-customer

### Publisher Configuration

If your app is publishing messages to a Pub/Sub topic, then you need to add Google API credentials to your project. If this application is strictly subscribing to topics, then skip to the next section.

1. [Create Credentials](http://i.imgur.com/WNwhduu.gifv) under :fa-navicon: > API Manager > [Credentials](https://console.cloud.google.com/apis/credentials).
2. In your `.env` file, add variables for the Google credentials file path.

        GOOGLE_APPLICATION_CREDENTIALS=/Users/lebronjames/google-service-account.json

### Subscriber Configuration

1. Start ngrok so Google Pub/Sub can reach your localhost.
2. Follow the steps to verify the ngrok url in the [Google Search Console](https://www.google.com/webmasters/tools)
3. Add the ngrok url under :fa-navicon: > API Manager > Credentials > [Domain verification](https://console.cloud.google.com/apis/credentials/domainverification)

        GOOGLE_PUB_SUB_SUBSCRIBER_TOKEN=7RWfH4yxnXXsep5k3LpVxv7oSlnhyFPFeHda87i3Vc

## FAQ

I see an error that says `Target [GenTux\PubSub\Contracts\PubSub] is not instantiable`.

> Be sure that the Service Provider is added to `config/app.php`.
