<?php

namespace Enqueue\AzureSserviceBus\Tests;

use Enqueue\AzureServiceBus\AzureServiceBusConnectionFactory;
use Enqueue\AzureServiceBus\AzureServiceBusContext;
use Enqueue\Test\ClassExtensionTrait;
use Interop\Queue\ConnectionFactory;
use PHPUnit\Framework\TestCase;

class AzureServiceBusConnectionFactoryTest extends TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementConnectionFactoryInterface()
    {
        $this->assertClassImplements(ConnectionFactory::class, AzureServiceBusConnectionFactory::class);
    }

    public function testCreateContext()
    {
        $connectionFactory = new AzureServiceBusConnectionFactory(
            'Endpoint=http://test.fr;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=test'
        );
        $context = $connectionFactory->createContext();
        $this->assertSame(AzureServiceBusContext::class, get_class($context));
    }
}
