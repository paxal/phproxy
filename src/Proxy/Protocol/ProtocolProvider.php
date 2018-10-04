<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Protocol;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use React\Socket\ConnectionInterface;

final class ProtocolProvider
{
    public static function get(ConnectionInterface $connection, ProxyRequest $request): Protocol
    {
        if ('CONNECT' === $request->getMethod()) {
            return new ConnectProtocol($connection, $request);
        }

        return new DirectProtocol($connection, $request);
    }
}
