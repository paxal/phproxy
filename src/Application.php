<?php

declare(strict_types=1);

namespace Paxal\Phproxy;

use Paxal\Phproxy\Command\ProxyCommand;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    private LoopInterface $loop;

    public function __construct()
    {
        parent::__construct('PHPROXY', '@package_version@');

        $this->loop = Loop::get();

        $command = new ProxyCommand($this->loop);
        $this->add($command);
        $this->setDefaultCommand((string) $command->getName());
    }
}
