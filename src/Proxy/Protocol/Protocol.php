<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Protocol;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use React\Socket\ConnectionInterface;

interface Protocol
{
    public function __construct(ConnectionInterface $connection, ProxyRequest $request);

    public function getTargetHost(): string;

    public function onClose(callable $afterClosedOnData): Protocol;
}
