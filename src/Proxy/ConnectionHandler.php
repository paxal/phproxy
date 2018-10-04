<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy;

use React\Socket\ConnectionInterface;

final class ConnectionHandler
{
    /**
     * @var DataHandlerFactory
     */
    private $dataHandlerFactory;

    public function __construct(DataHandlerFactory $dataHandlerFactory)
    {
        $this->dataHandlerFactory = $dataHandlerFactory;
    }

    public function __invoke(ConnectionInterface $connection): void
    {
        $connection->on('data', $this->dataHandlerFactory->create($connection));
    }
}
