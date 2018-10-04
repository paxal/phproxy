<?php

declare(strict_types=1);

namespace Paxal\Phproxy;

use Paxal\Phproxy\Command\ProxyCommand;
use React\EventLoop\Factory;
use Symfony\Component\Console\Application as BaseApplication;

final class Application extends BaseApplication
{
    private $loop;

    public function __construct()
    {
        parent::__construct('PHPROXY', '@package_version@');

        $this->loop = Factory::create();

        $command = new ProxyCommand($this->loop);
        $this->add($command);
        $this->setDefaultCommand($command->getName());
    }
}
