<?php

namespace Enqueue\AzureStorage\Tests;

use Enqueue\AzureServiceBus\AzureServiceBusDestination;
use Enqueue\Test\ClassExtensionTrait;
use Interop\Queue\Queue;
use Interop\Queue\Topic;

class AzureServiceBusDestinationTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementsTopicAndQueueInterfaces()
    {
        $this->assertClassImplements(Topic::class, AzureServiceBusDestination::class);
        $this->assertClassImplements(Queue::class, AzureServiceBusDestination::class);
    }

    public function testShouldReturnNameSetInConstructor()
    {
        $destination = new AzureServiceBusDestination('aDestinationName');

        $this->assertSame('aDestinationName', $destination->getName());
        $this->assertSame('aDestinationName', $destination->getQueueName());
        $this->assertSame('aDestinationName', $destination->getTopicName());
    }
}
