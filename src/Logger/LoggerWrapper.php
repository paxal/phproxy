<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class LoggerWrapper extends AbstractLogger
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function log($level, $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}
