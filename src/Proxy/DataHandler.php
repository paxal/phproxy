<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy;

use Paxal\Phproxy\Proxy\Authenticator\Authenticator;
use Paxal\Phproxy\Proxy\Protocol\ProtocolProvider;
use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use Paxal\Phproxy\Translator\TranslatorInterface;
use Psr\Log\LoggerInterface;
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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoopInterface $loop,
        TranslatorInterface $translator,
        Authenticator $authenticator,
        ConnectionInterface $connection,
        LoggerInterface $logger
    ) {
        $this->loop = $loop;
        $this->translator = $translator;
        $this->authenticator = $authenticator;
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function __invoke(string $data): void
    {
        try {
            $request = ProxyRequest::create($data);

            $this->logger->info('{method} {uri} {protocol}', [
                'method' => $request->getMethod(),
                'uri' => $request->getUri(),
                'protocol' => $request->getProtocol(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Unrecognized command', ['error' => $e->getMessage()]);
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
        $this->logger->notice('Redirecting : {uri} => {target}', ['uri' => $request->getUri(), 'target' => $targetHost]);
        (new Connector($this->loop))
            ->connect($targetHost)
            ->then(
                $protocol,
                function (\Exception $exception): void {
                    $this->sendError(500, 'Unable to connect', $exception->getMessage());
                }
            );
    }

    /**
     * @param array<string, string[]|string> $headers
     */
    private function sendError(int $status, string $statusText, string $content = '', string $contentType = 'text/plain', array $headers = []): void
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
