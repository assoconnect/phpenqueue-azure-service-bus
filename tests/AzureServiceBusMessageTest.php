<?php
declare(strict_types=1);

namespace Enqueue\AzureStorage\Tests;

use Enqueue\AzureServiceBus\AzureServiceBusMessage;
use Enqueue\Test\ClassExtensionTrait;
use Interop\Queue\Message;

class AzureServiceBusMessageTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageInterface()
    {
        $this->assertClassImplements(Message::class, AzureServiceBusMessage::class);
    }

    public function testCouldConstructMessageWithoutArguments()
    {
        $message = new AzureServiceBusMessage();

        $this->assertSame('', $message->getBody());
        $this->assertSame([], $message->getProperties());
        $this->assertSame([], $message->getHeaders());
    }

    public function testCouldBeConstructedWithOptionalArguments()
    {
        $message = new AzureServiceBusMessage('theBody', ['barProp' => 'barPropVal'], ['fooHeader' => 'fooHeaderVal']);

        $this->assertSame('theBody', $message->getBody());
        $this->assertSame(['barProp' => 'barPropVal'], $message->getProperties());
        $this->assertSame(['fooHeader' => 'fooHeaderVal'], $message->getHeaders());
    }

    public function testShouldSetCorrelationIdAsHeader()
    {
        $message = new AzureServiceBusMessage();
        $message->setCorrelationId('the-correlation-id');

        $this->assertSame(['correlation_id' => 'the-correlation-id'], $message->getHeaders());
    }

    public function testCouldSetMessageIdAsHeader()
    {
        $message = new AzureServiceBusMessage();
        $message->setMessageId('the-message-id');

        $this->assertSame(['message_id' => 'the-message-id'], $message->getHeaders());
    }

    public function testCouldSetTimestampAsHeader()
    {
        $message = new AzureServiceBusMessage();
        $message->setTimestamp(12345);

        $this->assertSame(['timestamp' => 12345], $message->getHeaders());
    }

    public function testShouldSetReplyToAsHeader()
    {
        $message = new AzureServiceBusMessage();
        $message->setReplyTo('theQueueName');

        $this->assertSame(['reply_to' => 'theQueueName'], $message->getHeaders());
    }

    public function testGetMessageText()
    {
        $message = new AzureServiceBusMessage();
        $message->setBody('body');
        $message->setProperties([]);

        $this->assertSame(base64_encode(json_encode([
            'body'          => 'body',
            'properties'    => []
        ])), $message->getMessageText());
    }

    public function testDeliveryDelay()
    {
        $message = new AzureServiceBusMessage();
        $message->setDeliveryDelay(100);
        $this->assertSame(100, $message->getDeliveryDelay());
    }

    public function testTimeToLive()
    {
        $message = new AzureServiceBusMessage();
        $message->setTimeToLive(100);
        $this->assertSame(100, $message->getTimeToLive());
    }
}
