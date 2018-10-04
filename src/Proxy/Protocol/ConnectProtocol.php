<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Protocol;

final class ConnectProtocol extends AbstractProtocol
{
    public function getTargetHost(): string
    {
        return $this->request->getUri();
    }

    protected function handle()
    {
        $this->local->write("{$this->request->getProtocol()} 200 OK\r\nProxy-agent: phproxy\r\n\r\n");
        $this->remote->write($this->request->getBody());

        $this->pipe();
    }
}
