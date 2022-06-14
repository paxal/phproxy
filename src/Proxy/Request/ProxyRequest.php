<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Request;

use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Proxy request representation.
 */
final class ProxyRequest
{
    private string $method;
    private string $uri;
    private string $protocol;
    private HeaderBag $headers;
    private string $body;

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
        $firstLine = array_shift($headersLines);
        $headersLines = array_filter($headersLines);

        $doesMatch = (bool) preg_match('@^(?<METHOD>.*?) (?<URI>.*?) (?<PROTOCOL>.*?)$@', $firstLine, $matches);
        if (!$doesMatch) {
            throw new \InvalidArgumentException('Operation not supported');
        }

        return new self($matches['METHOD'], $matches['URI'], $matches['PROTOCOL'], static::parseHeaders($headersLines), $body);
    }

    /**
     * @param list<string> $headers
     */
    private static function parseHeaders(array $headers): HeaderBag
    {
        $bag = new HeaderBag();
        foreach ($headers as $headerString) {
            $headerParts = explode(':', $headerString, 2);
            $bag->set($headerParts[0], trim($headerParts[1] ?? ''));
        }

        return $bag;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getHeaders(): HeaderBag
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
