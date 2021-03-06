<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;

final class PublicAuthenticator implements Authenticator
{
    public function isAuthorized(ProxyRequest $request): bool
    {
        return true;
    }
}
