<?php

namespace Enqueue\AzureStorage\Tests\Driver;

use Enqueue\AzureServiceBus\AzureServiceBusContext;
use Enqueue\AzureServiceBus\Driver\AzureServiceBusDriver;
use Enqueue\Client\Config;
use Enqueue\Client\RouteCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class AzureServiceBusDriverTest.
 */
class AzureServiceBusDriverTest extends TestCase
{
    public function testCreateTransportQueueName(): void
    {
        $context = $this->createContextMock();
        $driver =
            new AzureServiceBusDriver(
                $context,
                new Config('enqueue', '.', 'app', 'topic', 'queue', 'default', 'processor', [], []),
                new RouteCollection([])
            );
        $context
            ->expects($this->once())
            ->method('createQueue')
            ->with('enqueue-dot-app-dot-test-queue');

        $driver->createQueue('test_queue');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AzureServiceBusContext
     */
    private function createContextMock()
    {
        return $this->createMock(AzureServiceBusContext::class);
    }
}
