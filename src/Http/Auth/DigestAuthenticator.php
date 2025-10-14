<?php

namespace Saloon\Http\Auth;

use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Exceptions\SaloonException;

class DigestAuthenticator implements Authenticator
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
     * @var string
     */
    public $digest;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     * @param string $digest
     */
    public function __construct(
        $username,
        $password,
        $digest
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->digest = $digest;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     *
     * @throws SaloonException
     */
    public function set(PendingRequest $pendingRequest)
    {
        if (! $pendingRequest->getConnector()->sender() instanceof GuzzleSender) {
            throw new SaloonException('The DigestAuthenticator is only supported when using the GuzzleSender.');
        }

        // Note: This authenticator is currently using Guzzle to power the
        // authentication. This will be replaced later in Saloon v3 with
        // a first-party implementation of digest authentication.

        // See: https://docs.guzzlephp.org/en/stable/request-options.html#auth

        $pendingRequest->config()->add(RequestOptions::AUTH, [$this->username, $this->password, $this->digest]);
    }
}
