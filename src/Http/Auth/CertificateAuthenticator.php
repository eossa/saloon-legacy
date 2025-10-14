<?php

namespace Saloon\Http\Auth;

use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Exceptions\SaloonException;

class CertificateAuthenticator implements Authenticator
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string|null
     */
    public $password;

    /**
     * Constructor
     *
     * @param string $path
     * @param string|null $password
     */
    public function __construct(
        $path,
        $password = null
    ) {
        $this->path = $path;
        $this->password = $password;
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
            throw new SaloonException('The CertificateAuthenticator is only supported when using the GuzzleSender.');
        }

        // See: https://docs.guzzlephp.org/en/stable/request-options.html#cert

        $path = $this->path;
        $password = $this->password;

        $certificate = is_string($password) ? [$path, $password] : $path;

        $pendingRequest->config()->add(RequestOptions::CERT, $certificate);
    }
}
