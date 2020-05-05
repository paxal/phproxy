<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

final class AuthenticatorFactory
{
    /**
     * Creates authenticator. If any credential is given, it will be considered as basic.
     *
     * @param string[] $credentials
     */
    public static function create(array $credentials, string $type = 'basic'): Authenticator
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
