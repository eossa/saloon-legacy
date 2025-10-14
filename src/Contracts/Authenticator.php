<?php

namespace Saloon\Contracts;

use Saloon\Http\PendingRequest;

interface Authenticator
{
    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest);
}
