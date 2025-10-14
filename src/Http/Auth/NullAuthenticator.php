<?php

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class NullAuthenticator implements Authenticator
{
    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        //
    }
}
