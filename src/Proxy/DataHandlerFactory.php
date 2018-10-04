<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy;

use Paxal\Phproxy\Proxy\Authenticator\Authenticator;
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

    public function __construct(LoopInterface $loop, TranslatorInterface $translator, Authenticator $authenticator)
    {
        $this->loop = $loop;
        $this->translator = $translator;
        $this->authenticator = $authenticator;
    }

    public function create(ConnectionInterface $connection): DataHandler
    {
        return new DataHandler($this->loop, $this->translator, $this->authenticator, $connection);
    }
}
