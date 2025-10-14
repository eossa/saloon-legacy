<?php

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class HeaderAuthenticator implements Authenticator
{
    /**
     * Constructor
     */
    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string
     */
    public $headerName;

    /**
     * @param string $accessToken
     * @param string $headerName
     */
    public function __construct(
        $accessToken,
        $headerName = 'Authorization'
    ) {
        $this->accessToken = $accessToken;
        $this->headerName = $headerName;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add($this->headerName, $this->accessToken);
    }
}
