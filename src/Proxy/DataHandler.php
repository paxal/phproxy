<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy;

use Paxal\Phproxy\Proxy\Authenticator\Authenticator;
use Paxal\Phproxy\Proxy\Protocol\ProtocolProvider;
use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use Paxal\Phproxy\Translator\TranslatorInterface;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles every data that come into the proxy connection.
 */
final class DataHandler
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
     * @var ConnectionInterface
     */
    private $connection;

    public function __construct(LoopInterface $loop, TranslatorInterface $translator, Authenticator $authenticator, ConnectionInterface $connection)
    {
        $this->loop = $loop;
        $this->translator = $translator;
        $this->authenticator = $authenticator;
        $this->connection = $connection;
    }

    public function __invoke($data): void
    {
        try {
            $request = ProxyRequest::create($data);
        } catch (\Throwable $e) {
            $this->sendError(500, 'Unrecognized command : '.$e->getMessage());

            return;
        }

        $this->connection->removeListener('data', $this);

        if (!$this->authenticator->isAuthorized($request)) {
            $this->sendError(
                407,
                'Authentication required',
                '<h1>Forbidden</h1>',
                'text/html',
                ['Proxy-Authenticate' => 'Basic realm="Proxy password"']
            );

            return;
        }

        try {
            $protocol = ProtocolProvider::get($this->connection, $request);
        } catch (\Throwable $exception) {
            $this->sendError(500, $exception->getMessage());

            return;
        }
        $protocol->onClose($this);

        $targetHost = $this->translator->translate($protocol->getTargetHost());
        error_log("{$request->getUri()} -> {$targetHost}");
        (new Connector($this->loop))
            ->connect($targetHost)
            ->then(
                $protocol,
                function (\Exception $exception) {
                    $this->sendError(500, 'Unable to connect', $exception->getMessage());
                }
            );
    }

    private function sendError(int $status, string $statusText, string $content = '', string $contentType = 'text/plain', array $headers = [])
    {
        $response = Response::create(
            $content,
            $status,
            ['Connection' => 'close', 'Content-type' => $contentType, 'Proxy-agent' => 'Phproxy']
        );
        $response->setStatusCode($status, $statusText);
        $response->headers->add($headers);
        $response->headers->set('Content-length', (string) strlen($content));
        $this->connection->write((string) $response);
    }
}
