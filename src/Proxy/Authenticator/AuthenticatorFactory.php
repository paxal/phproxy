<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

final class AuthenticatorFactory
{
    public static function create(array $credentials, string $type = 'Basic'): Authenticator
    {
        if (0 !== count($credentials)) {
            switch ($type) {
                case 'basic':
                default:
                    return new BasicAuthenticator($credentials);
            }
        }

        return new PublicAuthenticator();
    }
}
