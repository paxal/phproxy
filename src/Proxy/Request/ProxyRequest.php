<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Request;

use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Proxy request representation.
 */
final class ProxyRequest
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var HeaderBag
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    private function __construct(string $method, string $uri, string $protocol, HeaderBag $headers, string $body)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->protocol = $protocol;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function create(string $data): self
    {
        $parts = explode("\r\n\r\n", $data, 2);
        $headers = $parts[0];
        $body = $parts[1] ?? '';

        $headersLines = explode("\r\n", $headers);
        \assert(is_array($headersLines));
        $firstLine = array_shift($headersLines);

        $doesMatch = preg_match('@^(?<METHOD>.*?) (?<URI>.*?) (?<PROTOCOL>.*?)$@', (string) $firstLine, $matches);
        if (!$doesMatch) {
            throw new \InvalidArgumentException('Operation not supported');
        }

        return new self($matches['METHOD'], $matches['URI'], $matches['PROTOCOL'], static::parseHeaders($headersLines), $body);
    }

    private static function parseHeaders(array $headers): HeaderBag
    {
        $bag = new HeaderBag();
        foreach ($headers as $headerString) {
            $headerParts = explode(':', $headerString, 2);
            $bag->set($headerParts[0], trim($headerParts[1] ?? ''));
        }

        return $bag;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return HeaderBag
     */
    public function getHeaders(): HeaderBag
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}
