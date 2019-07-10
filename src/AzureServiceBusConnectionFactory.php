<?php
declare(strict_types=1);

namespace Enqueue\AzureServiceBus;

use Interop\Queue\ConnectionFactory;
use Interop\Queue\Context;
use WindowsAzure\Common\ServicesBuilder;

class AzureServiceBusConnectionFactory implements ConnectionFactory
{
    /**
     * @var string
     */
    protected $connectionString;

    public function __construct(string $connectionString)
    {
        $this->connectionString = $connectionString;
    }

    public function createContext(): Context
    {
        $client = ServicesBuilder::getInstance()->createServiceBusService($this->connectionString);

        return new AzureServiceBusContext($client);
    }
}
