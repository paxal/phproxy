<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

final class AuthenticatorFactory
{
    /**
     * @param list<string> $credentials
     */
    public static function create(array $credentials, string $type = BasicAuthenticator::TYPE): Authenticator
    {
        if (0 !== count($credentials)) {
            return match (strtolower($type)) {
                BasicAuthenticator::TYPE => new BasicAuthenticator($credentials),
                default => throw new \LogicException(),
            };
        }

        return new PublicAuthenticator();
    }
}
