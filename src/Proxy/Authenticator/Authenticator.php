<?php

declare(strict_types=1);

namespace Paxal\Phproxy\Proxy\Authenticator;

use Paxal\Phproxy\Proxy\Request\ProxyRequest;

interface Authenticator
{
    /**
     * Authorize a proxy request.
     */
    public function isAuthorized(ProxyRequest $request): bool;
}
