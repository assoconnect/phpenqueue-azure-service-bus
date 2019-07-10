<?php

namespace Enqueue\AzureStorage\Tests;

use Enqueue\AzureServiceBus\AzureServiceBusConsumer;
use Enqueue\AzureServiceBus\AzureServiceBusContext;
use Enqueue\AzureServiceBus\AzureServiceBusDestination;
use Enqueue\AzureServiceBus\AzureServiceBusProducer;
use Enqueue\Null\NullQueue;
use Enqueue\Null\NullTopic;
use Enqueue\Test\ClassExtensionTrait;
use Interop\Queue\Context;
use Interop\Queue\Exception\PurgeQueueNotSupportedException;
use Interop\Queue\Message;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\TemporaryQueueNotSupportedException;
use Interop\Queue\Exception\SubscriptionConsumerNotSupportedException;
use Interop\Queue\Queue;
use WindowsAzure\ServiceBus\Internal\IServiceBus;

class AzureServiceBusContextTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementContextInterface()
    {
        $this->assertClassImplements(Context::class, AzureServiceBusContext::class);
    }

    public function testShouldAllowCreateEmptyMessage()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $message = $context->createMessage();

        $this->assertInstanceOf(Message::class, $message);

        $this->assertSame('', $message->getBody());
        $this->assertSame([], $message->getProperties());
        $this->assertSame([], $message->getHeaders());
    }

    public function testShouldAllowCreateCustomMessage()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $message = $context->createMessage('theBody', ['aProp' => 'aPropVal'], ['aHeader' => 'aHeaderVal']);

        $this->assertInstanceOf(Message::class, $message);

        $this->assertSame('theBody', $message->getBody());
        $this->assertSame(['aProp' => 'aPropVal'], $message->getProperties());
        $this->assertSame(['aHeader' => 'aHeaderVal'], $message->getHeaders());
    }

    public function testShouldCreateQueue()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $queue = $context->createQueue('aQueue');

        $this->assertInstanceOf(AzureServiceBusDestination::class, $queue);
        $this->assertSame('aQueue', $queue->getQueueName());
    }

    public function testShouldAllowCreateTopic()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $topic = $context->createTopic('aTopic');

        $this->assertInstanceOf(AzureServiceBusDestination::class, $topic);
        $this->assertSame('aTopic', $topic->getTopicName());
    }

    public function testThrowNotImplementedOnCreateTmpQueueCall()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $this->expectException(TemporaryQueueNotSupportedException::class);
        $this->expectExceptionMessage('The provider does not support temporary queue feature');

        $context->createTemporaryQueue();
    }

    public function testShouldCreateProducer()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $producer = $context->createProducer();

        $this->assertInstanceOf(AzureServiceBusProducer::class, $producer);
    }

    public function testShouldThrowIfNotAzureStorageDestinationGivenOnCreateConsumer()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $this->expectException(InvalidDestinationException::class);

        $consumer = $context->createConsumer(new NullQueue('aQueue'));

        $this->assertInstanceOf(AzureServiceBusConsumer::class, $consumer);
    }

    public function testShouldCreateConsumer()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $queue = $context->createQueue('aQueue');

        $consumer = $context->createConsumer($queue);

        $this->assertInstanceOf(AzureServiceBusConsumer::class, $consumer);
    }

    public function testThrowIfNotAzureStorageDestinationGivenOnDeleteQueue()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $this->expectException(InvalidDestinationException::class);
        $context->deleteQueue(new NullQueue('aQueue'));
    }

    public function testShouldAllowDeleteQueue()
    {
        $proxyMock = $this->createQueueRestProxyMock();
        $context = new AzureServiceBusContext($proxyMock);

        $queue = $context->createQueue('aQueueName');

        // Check, that delete queue command actually is invoked on client.
        $proxyMock
            ->expects($this->once())
            ->method('deleteQueue')
            ->with($queue);

        $context->deleteQueue($queue);
    }

    public function testThrowIfNotAzureStorageDestinationGivenOnDeleteTopic()
    {
        $client = $this->createQueueRestProxyMock();
        $context = new AzureServiceBusContext($client);

        $this->expectException(InvalidDestinationException::class);
        $context->deleteTopic(new NullTopic('aTopic'));
    }

    public function testShouldAllowDeleteTopic()
    {
        $proxyMock = $this->createQueueRestProxyMock();
        $context = new AzureServiceBusContext($proxyMock);

        $topic = $context->createTopic('aTopicName');

        // Check, that delete queue command actually is invoked on client.
        $proxyMock
            ->expects($this->once())
            ->method('deleteQueue')
            ->with($topic);

        $context->deleteTopic($topic);
    }

    public function testShouldReturnNotSupportedSubscriptionConsumerInstance()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $this->expectException(SubscriptionConsumerNotSupportedException::class);
        $this->expectExceptionMessage('The provider does not support subscription consumer.');

        $context->createSubscriptionConsumer();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IServiceBus
     */
    private function createQueueRestProxyMock()
    {
        return $this->createMock(IServiceBus::class);
    }

    public function testShouldReturnNotSupportedPurgeQueue()
    {
        $context = new AzureServiceBusContext($this->createQueueRestProxyMock());

        $this->expectException(PurgeQueueNotSupportedException::class);
        $this->expectExceptionMessage('The provider does not support purge queue.');

        $context->purgeQueue($this->createMock(Queue::class));
    }
}
