<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;

final class BasicAuthenticator implements Authenticator
{
    /**
     * @var array<string, array-key>
     */
    private $credentials;

    /**
     * @param array<string> $credentials
     */
    public function __construct(array $credentials)
    {
        $this->credentials = array_flip($this->encode($credentials));
    }

    /**
     * @param array<string> $credentials
     *
     * @return array<string, array-key>
     */
    private function encode(array $credentials): array
    {
        return array_map(
            function (string $credentials): string {
                return base64_encode($credentials);
            },
            $credentials
        );
    }

    public function isAuthorized(ProxyRequest $request): bool
    {
        $headerValue = $request->getHeaders()->get('proxy-authorization', null);
        if (!\is_string($headerValue)) {
            return false;
        }
        $doesMatch = (bool) \preg_match('@^Basic\s+(?<CREDENTIALS>.*?)\s*$@im', $headerValue, $matches);

        return $doesMatch && $this->areCredentialsValid($matches['CREDENTIALS'] ?? '');
    }

    private function areCredentialsValid(string $credentials): bool
    {
        return \array_key_exists($credentials, $this->credentials);
    }
}
