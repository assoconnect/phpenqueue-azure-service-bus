<?php
declare(strict_types=1);

namespace Enqueue\AzureServiceBus;

use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Destination;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\PurgeQueueNotSupportedException;
use Interop\Queue\Exception\SubscriptionConsumerNotSupportedException;
use Interop\Queue\Exception\TemporaryQueueNotSupportedException;
use Interop\Queue\Message;
use Interop\Queue\Producer;
use Interop\Queue\Queue;
use Interop\Queue\SubscriptionConsumer;
use Interop\Queue\Topic;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\QueueInfo;
use WindowsAzure\ServiceBus\Models\TopicInfo;

class AzureServiceBusContext implements Context
{
    /**
     * @var IServiceBus
     */
    protected $client;

    public function __construct(IServiceBus $client)
    {
        $this->client = $client;
    }

    public function createMessage(string $body = '', array $properties = [], array $headers = []): Message
    {
        $message = new AzureServiceBusMessage();
        $message->setBody($body);
        $message->setProperties($properties);
        $message->setHeaders($headers);
        return $message;
    }

    public function createTopic(string $topicName): Topic
    {
        $topicInfo = new TopicInfo($topicName);
        $this->client->createTopic($topicInfo);

        return new AzureServiceBusDestination($topicName);
    }

    public function createQueue(string $queueName): Queue
    {
        $queueInfo = new QueueInfo($queueName);
        $this->client->createQueue($queueInfo);

        return new AzureServiceBusDestination($queueName);
    }


    /**
     * @param AzureServiceBusDestination $queue
     *
     * @throws InvalidDestinationException Thrown, when destination is incompatible with the driver.
     */
    public function deleteQueue(Queue $queue): void
    {
        InvalidDestinationException::assertDestinationInstanceOf($queue, AzureServiceBusDestination::class);

        $this->client->deleteQueue($queue);
    }

    /**
     * @param AzureServiceBusDestination $topic
     *
     * @throws InvalidDestinationException Thrown, when destination is incompatible with the driver.
     */
    public function deleteTopic(Topic $topic): void
    {
        InvalidDestinationException::assertDestinationInstanceOf($topic, AzureServiceBusDestination::class);

        $this->client->deleteQueue($topic);
    }

    /**
     * @inheritdoc
     */
    public function createTemporaryQueue(): Queue
    {
        throw TemporaryQueueNotSupportedException::providerDoestNotSupportIt();
    }

    public function createProducer(): Producer
    {
        return new AzureServiceBusProducer($this->client);
    }

    /**
     * @param AzureServiceBusDestination $destination
     *
     * @return Consumer
     *
     * @throws InvalidDestinationException Thrown, when destination is incompatible with the driver.
     */
    public function createConsumer(Destination $destination): Consumer
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AzureServiceBusDestination::class);

        return new AzureServiceBusConsumer($this->client, $destination, $this);
    }

    /**
     * @inheritdoc
     */
    public function createSubscriptionConsumer(): SubscriptionConsumer
    {
        throw SubscriptionConsumerNotSupportedException::providerDoestNotSupportIt();
    }

    /**
     * @inheritdoc
     */
    public function purgeQueue(Queue $queue): void
    {
        throw PurgeQueueNotSupportedException::providerDoestNotSupportIt();
    }

    public function close(): void
    {
    }
}
