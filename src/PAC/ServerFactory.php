<?php

declare(strict_types=1);

namespace Paxal\Phproxy\PAC;

use Paxal\Phproxy\Translator\TranslatorBuilder;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ServerInterface;
use React\Socket\TcpServer;

/**
 * PAC HTTP Server Factory.
 */
class ServerFactory
{
    const CRLF = "\r\n";

    /** @var LoopInterface */
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * Create a PAC HTTP Server.
     *
     * @param string            $binding           Binding host, eg ip:port
     * @param string            $proxyHost         The proxy host, as of remote-side view (eg external_ip:port)
     * @param TranslatorBuilder $translatorBuilder The translator builder
     *
     * @return ServerInterface
     */
    public function create(string $binding, string $proxyHost, TranslatorBuilder $translatorBuilder): ServerInterface
    {
        $contents = ResponseFactory::create($proxyHost, $translatorBuilder->getTranslatedDomains());
        $server = new TcpServer($binding, $this->loop);
        $server->on('connection', function (ConnectionInterface $connection) use ($contents): void {
            $this->handle($connection, $contents);
        });

        return $server;
    }

    private function handle(ConnectionInterface $connection, string $contents): void
    {
        // Hodor !
        $connection->on('data', function () use ($contents, $connection) {
            error_log('Connection from '.$connection->getRemoteAddress());
            $connection->end($contents);
        });
    }
}
