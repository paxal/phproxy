<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;

final class BasicAuthenticator implements Authenticator
{
    public const TYPE = 'basic';

    /** @var array<string, int> */
    private array $credentials;

    /**
     * @param list<string> $credentials
     */
    public function __construct(array $credentials)
    {
        $this->credentials = array_flip($this->encode($credentials));
    }

    /**
     * @param list<string> $credentials
     *
     * @return list<string>
     */
    private function encode(array $credentials): array
    {
        return array_map(
            fn (string $credentials): string => base64_encode($credentials),
            $credentials
        );
    }

    public function isAuthorized(ProxyRequest $request): bool
    {
        $headerValue = $request->getHeaders()->get('proxy-authorization');
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
