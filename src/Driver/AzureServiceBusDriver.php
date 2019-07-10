<?php

namespace Enqueue\AzureServiceBus\Driver;

use Enqueue\AzureServiceBus\AzureServiceBusContext;
use Enqueue\AzureServiceBus\AzureServiceBusDestination;
use Enqueue\Client\Driver\GenericDriver;

/**
 * @method AzureServiceBusContext getContext
 * @method AzureServiceBusDestination createQueue(string $name)
 */
class AzureServiceBusDriver extends GenericDriver
{
    public function __construct(AzureServiceBusContext $context, ...$args)
    {
        parent::__construct($context, ...$args);
    }

    /**
     * Create transport queue name.
     *
     * This driver replaces some queue name characters, which are not valid in Azure Storage queues.
     *
     * @param string $name
     * @param bool $prefix
     *
     * @return string
     */
    protected function createTransportQueueName(string $name, bool $prefix): string
    {
        $name = parent::createTransportQueueName($name, $prefix);

        return str_replace(['.', '_'], ['-dot-', '-'], $name);
    }
}
