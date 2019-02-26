<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Protocol;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use React\Socket\ConnectionInterface;

final class DirectProtocol extends AbstractProtocol
{
    /**
     * @var string
     */
    private $targetHost;

    public function __construct(ConnectionInterface $connection, ProxyRequest $request)
    {
        parent::__construct($connection, $request);
        $this->targetHost = $this->computeTargetHost();
    }

    public function getTargetHost(): string
    {
        return $this->targetHost;
    }

    private function computeTargetHost(): string
    {
        $scheme = (string) parse_url($this->request->getUri(), PHP_URL_SCHEME);
        $host = parse_url($this->request->getUri(), PHP_URL_HOST);
        $port = parse_url($this->request->getUri(), PHP_URL_PORT) ?? $this->getDefaultPort($scheme);

        return sprintf('%s:%d', $host, $port);
    }

    protected function handle(): void
    {
        $path = preg_replace('@^.*?://.*?/@', '/', $this->request->getUri());
        $dataToSend = sprintf(
            "%s %s %s\r\n%s\r\n%s",
            $this->request->getMethod(),
            $path,
            $this->request->getProtocol(),
            $this->getTargetHeaders(),
            $this->request->getBody()
        );

        $this->local->write("{$this->request->getProtocol()} 100 Continue\r\n\r\n");

        $this->remote->write($dataToSend);
        $this->pipe();
    }

    private function getTargetHeaders(): string
    {
        $headers = $this->request->getHeaders();
        foreach ($headers->keys() as $key) {
            if (0 === strpos(strtolower($key), 'proxy-')) {
                $headers->remove($key);
            }
        }
        $headers->remove('expect');

        return $headers->__toString();
    }

    /**
     * Tell which port to connect to.
     *
     * @param string $scheme The scheme
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getDefaultPort(string $scheme): int
    {
        switch ($scheme) {
            case 'http':
                return 80;

            default:
                throw new \Exception('Unsupported protocol');
        }
    }
}
