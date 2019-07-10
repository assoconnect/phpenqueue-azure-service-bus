# Azure Service Bus transport
Azure Service Bus transport is a messaging solution transport using Azure compatible with [Queue Interop](https://github.com/queue-interop/queue-interop)

[![Build Status](https://travis-ci.org/assoconnect/phpenqueue-azure-service-bus.svg?branch=master)](https://travis-ci.org/assoconnect/phpenqueue-azure-service-bus)
[![Coverage Status](https://coveralls.io/repos/github/assoconnect/phpenqueue-azure-service-bus/badge.svg?branch=master)](https://coveralls.io/github/assoconnect/phpenqueue-azure-service-bus?branch=master)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=assoconnect_phpenqueue-azure-service-bus&metric=alert_status)](https://sonarcloud.io/dashboard?id=assoconnect_phpenqueue-azure-service-bus)

The transport uses [Azure Service Bus](https://docs.microsoft.com/fr-fr/azure/service-bus-messaging/service-bus-php-how-to-use-queues) as a message broker.
It creates a collection (a queue or topic) there. It's a FIFO system (First In First Out).
 
* [Installation](#installation)
* [Create context](#create-context)
* [Send message to topic](#send-message-to-topic)
* [Send message to queue](#send-message-to-queue)
* [Send expiration message](#send-expiration-message)
* [Consume message](#consume-message)
* [Delete queue (purge messages)](#delete-queue-purge-messages)
* [Delete topic (purge messages)](#delete-topic-purge-messages)

## Installation

* With composer:

```bash
$ composer require assoconnect/phpenqueue-azure-service-bus
```

## Create context

```php
<?php
use WindowsAzure\Common\ServicesBuilder;

// connects to azure
$connectionString = "Endpoint=[yourEndpoint];SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=[Primary Key]";

$serviceBusRestProxy = ServicesBuilder::getInstance()->createServiceBusService($connectionString);

$context = $factory->createContext();

```

## Send message to topic

```php
<?php
/** @var \Enqueue\AzureStorage\AzureStorageContext $context */

$fooTopic = $context->createTopic('aTopic');
$message = $context->createMessage('Hello world!');

$context->createProducer()->send($fooTopic, $message);
```

## Send message to queue 

```php
<?php
/** @var \Enqueue\AzureStorage\AzureStorageContext $context */

$fooQueue = $context->createQueue('aQueue');
$message = $context->createMessage('Hello world!');

$context->createProducer()->send($fooQueue, $message);
```

## Send expiration message

```php
<?php
/** @var \Enqueue\AzureStorage\AzureStorageContext $context */
/** @var \Enqueue\AzureStorage\AzureStorageDestination $fooQueue */


$message = $context->createMessage('Hello world!');

$context->createProducer()
    ->setTimeToLive(60000) // 60 sec
    ->send($fooQueue, $message)
;
```

## Consume message:

```php
<?php
/** @var \Enqueue\AzureStorage\AzureStorageContext $context */

$fooQueue = $context->createQueue('aQueue');
$consumer = $context->createConsumer($fooQueue);

$message = $consumer->receiveNoWait();

// process a message

$consumer->acknowledge($message);
//$consumer->reject($message);
```

## Delete queue (purge messages):

```php
<?php
/** @var \Enqueue\AzureStorage\AzureStorageContext $context */

$fooQueue = $context->createQueue('aQueue');

$context->deleteQueue($fooQueue);
```

## Delete topic (purge messages):

```php
<?php
/** @var \Enqueue\AzureStorage\AzureStorageContext $context */

$fooTopic = $context->createTopic('aTopic');

$context->deleteTopic($fooTopic);
```

[back to index](../index.md)
