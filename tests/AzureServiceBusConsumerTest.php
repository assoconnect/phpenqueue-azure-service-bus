<?php

namespace Enqueue\AzureServiceBus\Tests;

use Enqueue\AzureServiceBus\AzureServiceBusConsumer;
use Enqueue\AzureServiceBus\AzureServiceBusContext;
use Enqueue\AzureServiceBus\AzureServiceBusDestination;
use Enqueue\Test\ClassExtensionTrait;
use function GuzzleHttp\Psr7\stream_for;
use Interop\Queue\Consumer;
use WindowsAzure\Queue\Models\WindowsAzureQueueMessage;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\ServiceBusRestProxy;

class AzureServiceBusConsumerTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementConsumerInterface()
    {
        $this->assertClassImplements(Consumer::class, AzureServiceBusConsumer::class);
    }

    public function testCouldBeConstructedWithContextAndDestinationAndPreFetchCountAsArguments()
    {
        $restProxy = $this->createQueueRestProxyMock();
        $azureServiceBusConsumer = new AzureServiceBusConsumer(
            $restProxy,
            new AzureServiceBusDestination('aQueue'),
            new AzureServiceBusContext($restProxy)
        );

        $this->assertSame('aQueue', $azureServiceBusConsumer->getQueue()->getQueueName());
    }

    public function testShouldReturnDestinationSetInConstructorOnGetQueue()
    {
        $destination = new AzureServiceBusDestination('aQueue');
        $restProxy = $this->createQueueRestProxyMock();
        $consumer = new AzureServiceBusConsumer($restProxy, $destination, new AzureServiceBusContext($restProxy));

        $this->assertSame($destination, $consumer->getQueue());
    }

    public function testReceiveNoWait()
    {
        $messageMock = new BrokeredMessage();
        $messageMock->setMessageId('testId');
        $messageMock->setDate('10 September 2000');
        $messageMock->setBody(
            stream_for(
                '<?xml version="1.0" encoding="utf-8"?><message>Hi mate, how are you?</message>'
            )
        );
        $messageMock->setDeliveryCount(2);
        $messageMock->setLockLocation('/testPath');
        $messageMock->setLockedUntilUtc(new \DateTime('+10 day'));
        $messageMock->setTimeToLive(1209600);

        $serviceBusRestProxy = $this->createQueueRestProxyMock();
        $serviceBusRestProxy
            ->expects($this->any())
            ->method('receiveQueueMessage')
            ->willReturn($messageMock)
        ;
        $consumer = new AzureServiceBusConsumer(
            $serviceBusRestProxy,
            new AzureServiceBusDestination('test'),
            new AzureServiceBusContext($serviceBusRestProxy)
        );
        $message = $consumer->receiveNoWait();
        $this->assertSame('Hi mate, how are you?', $message->getBody());
        $this->assertSame('testId', $message->getMessageId());
        $this->assertSame($messageMock, $message->getBrokeredMessage());
    }

    public function testAcknowledge()
    {
        $messageMock = new BrokeredMessage();
        $messageMock->setMessageId('testId');
        $messageMock->setDate('10 September 2000');
        $messageMock->setBody(
            stream_for(
                '<?xml version="1.0" encoding="utf-8"?><message>Hi mate, how are you?</message>'
            )
        );
        $messageMock->setDeliveryCount(2);
        $messageMock->setLockLocation('/testPath');
        $messageMock->setLockedUntilUtc(new \DateTime('+10 day'));
        $messageMock->setTimeToLive(1209600);

        $serviceBusRestProxy = $this->createQueueRestProxyMock();
        $serviceBusRestProxy
            ->expects($this->at(0))
            ->method('receiveQueueMessage')
            ->willReturn($messageMock)
        ;
        $serviceBusRestProxy
            ->expects($this->at(1))
            ->method('receiveQueueMessage')
            ->willReturn(new BrokeredMessage())
        ;
        $consumer = new AzureServiceBusConsumer(
            $serviceBusRestProxy,
            new AzureServiceBusDestination('test'),
            new AzureServiceBusContext($serviceBusRestProxy)
        );
        $message = $consumer->receiveNoWait();

        $consumer->acknowledge($message);
        $message = $consumer->receiveNoWait();
        $this->assertNull($message);
    }

    public function testReject()
    {
        $messageMock = new BrokeredMessage();
        $messageMock->setMessageId('testId');
        $messageMock->setDate('10 September 2000');
        $messageMock->setBody(
            stream_for(
                '<?xml version="1.0" encoding="utf-8"?><message>Hi mate, how are you?</message>'
            )
        );
        $messageMock->setDeliveryCount(2);
        $messageMock->setLockLocation('/testPath');
        $messageMock->setLockedUntilUtc(new \DateTime('+10 day'));
        $messageMock->setTimeToLive(1209600);

        $serviceBusRestProxy = $this->createQueueRestProxyMock();
        $serviceBusRestProxy
            ->expects($this->at(0))
            ->method('receiveQueueMessage')
            ->willReturn($messageMock)
        ;
        $serviceBusRestProxy
            ->expects($this->at(1))
            ->method('receiveQueueMessage')
            ->willReturn($messageMock)
        ;
        $serviceBusRestProxy
            ->expects($this->at(2))
            ->method('receiveQueueMessage')
            ->willReturn($messageMock)
        ;
        $consumer = new AzureServiceBusConsumer(
            $serviceBusRestProxy,
            new AzureServiceBusDestination('test'),
            new AzureServiceBusContext($serviceBusRestProxy)
        );
        $message = $consumer->receiveNoWait();

        // Reject and requeue
        $consumer->reject($message, true);
        $message = $consumer->receiveNoWait();
        $this->assertSame('testId', $message->getMessageId());

        // Reject and don't requeue
        $consumer->reject($message, false);
        $message = $consumer->receiveNoWait();
        $this->assertNull($message);
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServiceBusRestProxy
     */
    private function createQueueRestProxyMock()
    {
        return $this->createMock(ServiceBusRestProxy::class);
    }
}
