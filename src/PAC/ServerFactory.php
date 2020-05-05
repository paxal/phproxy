<?php

declare(strict_types=1);

namespace Paxal\Phproxy\PAC;

use Paxal\Phproxy\Translator\TranslatorBuilder;
use Psr\Log\LoggerInterface;
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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoopInterface $loop, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->logger = $logger;
    }

    /**
     * Create a PAC HTTP Server.
     *
     * @param string            $binding           Binding host, eg ip:port
     * @param string            $proxyHost         The proxy host, as of remote-side view (eg external_ip:port)
     * @param TranslatorBuilder $translatorBuilder The translator builder
     */
    public function create(string $binding, string $proxyHost, TranslatorBuilder $translatorBuilder): ServerInterface
    {
        $contents = ResponseFactory::create($proxyHost, $translatorBuilder->getTranslatedDomains());
        $server = new TcpServer($binding, $this->loop);
        $server->on('connection', function (ConnectionInterface $connection) use ($contents): void {
            $this->handle($connection, $contents);
        });

        $this->loop->futureTick(function () use ($binding, $contents): void {
            $this->logger->info('PAC Server listening on {binding}', ['binding' => $binding]);
            $this->logger->debug('PAC Contents :'.PHP_EOL.$contents);
        });

        return $server;
    }

    private function handle(ConnectionInterface $connection, string $contents): void
    {
        // Hodor !
        $connection->on('data', function () use ($contents, $connection): void {
            $this->logger->info('New PAC Connection from {remote}', ['remote' => $connection->getRemoteAddress()]);
            $connection->end($contents);
        });
    }
}
