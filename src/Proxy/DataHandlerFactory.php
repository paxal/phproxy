<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy;

use Paxal\Phproxy\Proxy\Authenticator\Authenticator;
use Paxal\Phproxy\Translator\TranslatorBuilder;
use Paxal\Phproxy\Translator\TranslatorInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

final class DataHandlerFactory
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoopInterface $loop, TranslatorBuilder $translatorBuilder, Authenticator $authenticator, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->translator = $translatorBuilder->build();
        $this->authenticator = $authenticator;
        $this->logger = $logger;
    }

    public function create(ConnectionInterface $connection): DataHandler
    {
        $this->logger->info('Proxy Connection from {remote}', ['remote' => $connection->getRemoteAddress()]);

        return new DataHandler($this->loop, $this->translator, $this->authenticator, $connection, $this->logger);
    }
}
