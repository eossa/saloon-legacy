<?php

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class TokenAuthenticator implements Authenticator
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $prefix;

    /**
     * @param string $token
     * @param string $prefix
     */
    public function __construct(
        $token,
        $prefix = 'Bearer'
    ) {
        $this->token = $token;
        $this->prefix = $prefix;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Authorization', trim($this->prefix . ' ' . $this->token));
    }
}
