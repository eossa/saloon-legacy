<?php

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class MultiAuthenticator implements Authenticator
{
    /**
     * Authenticators
     *
     * @var array<Authenticator>
     */
    protected $authenticators;

    /**
     * Constructor
     */
    public function __construct(Authenticator ...$authenticators)
    {
        $this->authenticators = $authenticators;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        foreach ($this->authenticators as $authenticator) {
            $authenticator->set($pendingRequest);
        }
    }
}
