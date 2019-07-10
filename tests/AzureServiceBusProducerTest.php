<?php

namespace Enqueue\AzureServiceBus\Tests;

use Enqueue\AzureServiceBus\AzureServiceBusDestination;
use Enqueue\AzureServiceBus\AzureServiceBusMessage;
use Enqueue\AzureServiceBus\AzureServiceBusProducer;
use Enqueue\Null\NullMessage;
use Enqueue\Null\NullQueue;
use Enqueue\Test\ClassExtensionTrait;
use Interop\Queue\Exception\PriorityNotSupportedException;
use Interop\Queue\Producer;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use PHPUnit\Framework\TestCase;
use WindowsAzure\ServiceBus\Internal\IServiceBus;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;

class AzureServiceBusProducerTest extends TestCase
{
    use ClassExtensionTrait;

    public function getProducer():AzureServiceBusProducer
    {
        return new AzureServiceBusProducer($this->createQueueRestProxyMock());
    }

    public function testShouldImplementProducerInterface()
    {
        $this->assertClassImplements(Producer::class, AzureServiceBusProducer::class);
    }

    public function testCouldBeConstructedWithQueueRestProxy()
    {
        $producer = $this->getProducer();

        $this->assertInstanceOf(AzureServiceBusProducer::class, $producer);
    }

    public function testThrowIfDestinationNotAzureServiceBusDestinationOnSend()
    {
        $producer = $this->getProducer();

        $this->expectException(InvalidDestinationException::class);
        $exceptionMessage =
            'The destination must be an instance of Enqueue\AzureServiceBus\AzureServiceBusDestination ';
        $exceptionMessage .= 'but got Enqueue\Null\NullQueue.';
        $this->expectExceptionMessage($exceptionMessage);
        $producer->send(new NullQueue('aQueue'), new AzureServiceBusMessage());
    }

    public function testThrowIfMessageNotAzureServiceBusMessageOnSend()
    {
        $producer = $this->getProducer();

        $this->expectException(InvalidMessageException::class);
        $exceptionMessage = 'The message must be an instance of Enqueue\AzureServiceBus\AzureServiceBusMessage ';
        $exceptionMessage .= 'but it is Enqueue\Null\NullMessage.';
        $this->expectExceptionMessage($exceptionMessage);
        $producer->send(new AzureServiceBusDestination('aQueue'), new NullMessage());
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IServiceBus
     */
    private function createQueueRestProxyMock()
    {
        return $this->createMock(IServiceBus::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IServiceBus
     */
    private function createQueueMessageMock()
    {
        $insertionDateMock = $this->createMock(\DateTime::class);
        $insertionDateMock
            ->expects($this->any())
            ->method('getTimestamp')
            ->willReturn(1542809366);
        
        $messageMock = $this->createMock(IServiceBus::class);
        $messageMock
            ->expects($this->any())
            ->method('getMessageId')
            ->willReturn('any');
        $messageMock
            ->expects($this->any())
            ->method('getMessageText')
            ->willReturn('aBody');
        $messageMock
            ->expects($this->any())
            ->method('getInsertionDate')
            ->willReturn($insertionDateMock);
        $messageMock
            ->expects($this->any())
            ->method('getDequeueCount')
            ->willReturn('any');
        $messageMock
            ->expects($this->any())
            ->method('getDequeueCount')
            ->willReturn('any');
        $messageMock
            ->expects($this->any())
            ->method('getExpirationDate')
            ->willReturn('any');
        $messageMock
            ->expects($this->any())
            ->method('getExpirationDate')
            ->willReturn('any');
        $messageMock
            ->expects($this->any())
            ->method('getTimeNextVisible')
            ->willReturn('any');
        return $messageMock;
    }

    public function testSetDeliveryDelayWhenDeliveryStrategyIsNotSet()
    {
        $producer = $this->getProducer();

        $this->assertSame($producer, $producer->setDeliveryDelay(null));

        $result = $producer->setDeliveryDelay(10000);

        $this->assertSame($producer, $result);
        $this->assertSame(10000, $result->getDeliveryDelay());
    }

    public function testShouldThrowExceptionOnSetPriorityWhenPriorityIsNotSet()
    {
        $producer = $this->getProducer();

        $this->assertSame($producer, $producer->setPriority(null));

        $this->expectException(PriorityNotSupportedException::class);
        $this->expectExceptionMessage('The provider does not support priority feature');
        $producer->setPriority(10000);
    }

    public function testGetPriority()
    {
        $producer = $this->getProducer();
        $this->assertNull($producer->getPriority());
    }

    public function testTimeToLive()
    {
        $producer = $this->getProducer();
        $producer->setTimeToLive(100);
        $this->assertSame(100, $producer->getTimeToLive());
    }

    public function testSend()
    {
        $messageMock = $this->createMock(IServiceBus::class);
        $messageMock
            ->expects($this->once())
            ->method('sendQueueMessage');

        $message = new AzureServiceBusMessage();
        $message->setBrokeredMessage(new BrokeredMessage());
        $producer =  new AzureServiceBusProducer($messageMock);
        $producer->setDeliveryDelay(100);
        $producer->setTimeToLive(100);
        $producer->send(new AzureServiceBusDestination('test'), $message);

        $this->assertSame(100, $message->getTimeToLive());
        $this->assertSame(100, $message->getDeliveryDelay());
    }
}
