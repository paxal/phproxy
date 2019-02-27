<?php

declare(strict_types=1);

namespace Paxal\Tests\Phproxy\Proxy\Request;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;
use PHPUnit\Framework\TestCase;

class ProxyRequestTest extends TestCase
{
    public function test()
    {
        $request = <<<REQUEST
GET http://10.129.22.122:8002/proxy.pac HTTP/1.1
Host: 10.129.22.122:8002
User-Agent: curl/7.64.0
Accept: */*
Proxy-Connection: Keep-Alive

REQUEST;

        $request = preg_replace("@(?<!\r)\n@", "\r\n", $request);

        $proxyRequest = ProxyRequest::create($request);
        self::assertSame('GET', $proxyRequest->getMethod());
        self::assertSame('http://10.129.22.122:8002/proxy.pac', $proxyRequest->getUri());
        self::assertSame('', $proxyRequest->getBody());
        self::assertSame('HTTP/1.1', $proxyRequest->getProtocol());
        self::assertSame([
            'host' => ['10.129.22.122:8002'],
            'user-agent' => ['curl/7.64.0'],
            'accept' => ['*/*'],
            'proxy-connection' => ['Keep-Alive'],
        ], $proxyRequest->getHeaders()->all());
    }
}
