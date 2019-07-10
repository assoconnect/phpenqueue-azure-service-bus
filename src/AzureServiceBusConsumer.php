<?php

declare(strict_types=1);

namespace Enqueue\AzureServiceBus;

use Interop\Queue\Consumer;
use Interop\Queue\Impl\ConsumerPollingTrait;
use Interop\Queue\Impl\ConsumerVisibilityTimeoutTrait;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Message;
use Interop\Queue\Queue;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\ReceiveMessageOptions;

class AzureServiceBusConsumer implements Consumer
{
    use ConsumerPollingTrait;
    use ConsumerVisibilityTimeoutTrait;

    /**
     * @var ServicesBuilder
     */
    protected $client;

    protected $queue;

    protected $context;

    public function __construct(IServiceBus $client, AzureServiceBusDestination $queue, AzureServiceBusContext $context)
    {
        $this->client = $client;
        $this->queue = $queue;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @inheritdoc
     */
    public function receiveNoWait(): ?Message
    {
        $options = new ReceiveMessageOptions();
        $options->setPeekLock();

        $message = $this->client->receiveQueueMessage($this->queue->getQueueName(), $options);
        if ($message) {
            $messageBody = new \SimpleXMLElement($message->getBody()->__toString());
            $messageProperties = $message->getProperties();

            $formattedMessage = new AzureServiceBusMessage();
            $formattedMessage->setBody((string)$messageBody[0]);
            $formattedMessage->setProperties($messageProperties);
            $formattedMessage->setHeaders([
                'dequeue_count' => $message->getDeliveryCount(),
                'expiration_date' => strtotime($message->getDate()) + $message->getTimeToLive(),
                'pop_receipt' => $message->getLockLocation(),
                'next_time_visible' => $message->getLockedUntilUtc(),
            ]);
            $formattedMessage->setMessageId($message->getMessageId());
            $formattedMessage->setTimestamp(strtotime($message->getDate()));
            $formattedMessage->setRedelivered($message->getDeliveryCount() > 1);
            $formattedMessage->setBrokeredMessage($message);


            return $formattedMessage;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function acknowledge(Message $message): void
    {
        InvalidMessageException::assertMessageInstanceOf($message, AzureServiceBusMessage::class);

        $this->client->deleteMessage($message->getBrokeredMessage());
    }

    /**
     * @inheritdoc
     */
    public function reject(Message $message, bool $requeue = false): void
    {
        InvalidMessageException::assertMessageInstanceOf($message, AzureServiceBusMessage::class);

        // We must acknowledge to remove the message from the queue
        if (false === $requeue) {
            $this->acknowledge($message);
        } else {
            $this->client->unlockMessage($message->getBrokeredMessage());
        }
    }
}
