<?php

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class AuthenticatePendingRequest
{
    /**
     * Authenticate the pending request
     *
     * @return PendingRequest
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        $authenticator = $pendingRequest->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
            $authenticator->set($pendingRequest);
        }

        return $pendingRequest;
    }
}
