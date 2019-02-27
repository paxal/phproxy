<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy;

use Paxal\Phproxy\Proxy\Authenticator\Authenticator;
use Paxal\Phproxy\Translator\TranslatorBuilder;
use Paxal\Phproxy\Translator\TranslatorInterface;
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

    public function __construct(LoopInterface $loop, TranslatorBuilder $translatorBuilder, Authenticator $authenticator)
    {
        $this->loop = $loop;
        $this->translator = $translatorBuilder->build();
        $this->authenticator = $authenticator;
    }

    public function create(ConnectionInterface $connection): DataHandler
    {
        error_log('Proxy Connection from '.$connection->getRemoteAddress());

        return new DataHandler($this->loop, $this->translator, $this->authenticator, $connection);
    }
}
