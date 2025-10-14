<?php

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class BasicAuthenticator implements Authenticator
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(
        $username,
        $password
    ) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Authorization', 'Basic ' . base64_encode($this->username . ':' . $this->password));
    }
}
