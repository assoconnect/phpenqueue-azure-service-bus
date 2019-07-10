<?php

namespace Enqueue\AzureStorage\Tests\Driver;

use Enqueue\AzureServiceBus\AzureServiceBusConnectionFactory;
use Enqueue\AzureServiceBus\Driver\AzureServiceBusDriverFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class AzureServiceBusDriverFactoryTest.
 */
class AzureServiceBusDriverFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new AzureServiceBusDriverFactory();
        $config['connection_string'] =
            'Endpoint=http://test.fr;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=test';
        $factory = $factory->create($config);

        $this->assertSame(AzureServiceBusConnectionFactory::class, get_class($factory));
    }
}
