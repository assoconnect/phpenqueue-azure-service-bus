<?php
declare(strict_types=1);

namespace Enqueue\AzureServiceBus;

use Interop\Queue\Impl\MessageTrait;
use Interop\Queue\Message;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;

class AzureServiceBusMessage implements Message
{
    use MessageTrait;

    /** @var BrokeredMessage */
    private $brokeredMessage;

    /**
     * @var int milliseconds
     */
    private $deliveryDelay;

    /**
     * @var int milliseconds
     */
    private $timeToLive;

    /**
     * @return BrokeredMessage
     */
    public function getBrokeredMessage(): BrokeredMessage
    {
        return $this->brokeredMessage;
    }

    /**
     * @param BrokeredMessage $brokeredMessage
     */
    public function setBrokeredMessage(BrokeredMessage $brokeredMessage): void
    {
        $this->brokeredMessage = $brokeredMessage;
    }
    
    public function __construct(string $body = '', array $properties = [], array $headers = [])
    {
        $this->body = $body;
        $this->properties = $properties;
        $this->headers = $headers;

        $this->redelivered = false;
    }

    public function getMessageText(): string
    {
        $messageText = [
            'body' => $this->body,
            'properties' => $this->properties,
        ];
        return base64_encode(json_encode($messageText));
    }

    public function getDeliveryDelay(): ?int
    {
        return $this->deliveryDelay;
    }

    /**
     * Set delay in milliseconds.
     * @param int|null $deliveryDelay
     */
    public function setDeliveryDelay(int $deliveryDelay = null): void
    {
        $this->deliveryDelay = $deliveryDelay;
    }

    /**
     * @return int
     */
    public function getTimeToLive(): ?int
    {
        return $this->timeToLive;
    }

    /**
     * Set time to live in milliseconds.
     * @param int|null $timeToLive
     */
    public function setTimeToLive(int $timeToLive = null): void
    {
        $this->timeToLive = $timeToLive;
    }
}
