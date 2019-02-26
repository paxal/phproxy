<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Protocol;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use React\Socket\ConnectionInterface;

abstract class AbstractProtocol implements Protocol
{
    /**
     * @var ConnectionInterface
     */
    protected $local;

    /**
     * @var ProxyRequest
     */
    protected $request;

    /**
     * @var ConnectionInterface
     */
    protected $remote;

    /**
     * @var callable
     */
    private $afterClosedOnData;

    public function __construct(ConnectionInterface $local, ProxyRequest $request)
    {
        $this->local = $local;
        $this->request = $request;
    }

    public function __invoke(ConnectionInterface $remote)
    {
        $this->remote = $remote;
        $this->handle();
    }

    abstract protected function handle();

    protected function pipe()
    {
        $this->local->pipe($this->remote);
        $this->remote->pipe($this->local, [/*'end' => false*/]);
        $this->remote->on('close', function () {
            $this->local->on('data', $this->afterClosedOnData);
        });
    }

    public function onClose(callable $afterClosedOnData): Protocol
    {
        $this->afterClosedOnData = $afterClosedOnData;

        return $this;
    }
}
