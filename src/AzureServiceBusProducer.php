<?php
declare(strict_types=1);

namespace Enqueue\AzureServiceBus;

use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Exception\PriorityNotSupportedException;
use Interop\Queue\Message;
use Interop\Queue\Destination;
use Interop\Queue\Producer;
use WindowsAzure\ServiceBus\Internal\IServiceBus;

class AzureServiceBusProducer implements Producer
{
    /**
     * @var IServiceBus
     */
    protected $client;

    protected $deliveryDelay;

    public function __construct(IServiceBus $client)
    {
        $this->client = $client;
    }

    /**
     * @throws InvalidDestinationException if a client uses this method with an invalid destination
     * @throws InvalidMessageException     if an invalid message is specified
     * @var AzureServiceBusDestination $destination
     * @var AzureServiceBusMessage $message
     */
    public function send(Destination $destination, Message $message): void
    {
        InvalidDestinationException::assertDestinationInstanceOf($destination, AzureServiceBusDestination::class);
        InvalidMessageException::assertMessageInstanceOf($message, AzureServiceBusMessage::class);

        if (null !== $this->deliveryDelay && null === $message->getDeliveryDelay()) {
            $message->setDeliveryDelay($this->deliveryDelay);
        }
        if (null !== $this->timeToLive && null === $message->getTimeToLive()) {
            $message->setTimeToLive($this->timeToLive);
        }

        $brokeredMessage = $message->getBrokeredMessage();
        // Send message.
        $this->client->sendQueueMessage($destination->getQueueName(), $brokeredMessage);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryDelay(int $deliveryDelay = null): Producer
    {
        $this->deliveryDelay = $deliveryDelay;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority = null): Producer
    {
        if (null === $priority) {
            return $this;
        }
        throw PriorityNotSupportedException::providerDoestNotSupportIt();
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): ?int
    {
        return null;
    }

    /**
     * @var integer
     */
    protected $timeToLive;

    /**
     * @inheritdoc
     */
    public function setTimeToLive(int $timeToLive = null): Producer
    {
        $this->timeToLive = $timeToLive;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTimeToLive(): ?int
    {
        return $this->timeToLive;
    }
}
