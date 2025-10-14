<?php

namespace Saloon\Http\Auth;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class QueryAuthenticator implements Authenticator
{
    /**
     * @var string
     */
    public $parameter;

    /**
     * @var string
     */
    public $value;

    /**
     * Constructor
     *
     * @param string $parameter
     * @param string $value
     */
    public function __construct(
        $parameter,
        $value
    ) {
        $this->parameter = $parameter;
        $this->value = $value;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        $pendingRequest->query()->add($this->parameter, $this->value);
    }
}
