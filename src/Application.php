<?php

declare(strict_types=1);

namespace Paxal\Phproxy;

use Paxal\Phproxy\Command\ProxyCommand;
use Paxal\Phproxy\Logger\LoggerWrapper;
use React\EventLoop\Factory;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

final class Application extends BaseApplication
{
    /** @var \React\EventLoop\LoopInterface */
    private $loop;
    /** @var LoggerWrapper */
    private $logger;

    public function __construct()
    {
        parent::__construct('PHPROXY', '@package_version@');

        $this->loop = Factory::create();
        $this->logger = new LoggerWrapper();

        $command = new ProxyCommand($this->loop, $this->logger);
        $this->add($command);
        $this->setDefaultCommand((string) $command->getName());
    }

    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        $this->logger->setLogger(new ConsoleLogger($output));

        return parent::doRunCommand($command, $input, $output);
    }
}
