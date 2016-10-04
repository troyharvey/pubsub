# PubSub for Lumen & Laravel

PubSub is a push notification system for your backend. `Publishers` add
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

Install `PubSub` using [Composer](https://getcomposer.org/).

```
composer require generationtux/pubsub
```

## Configuration

