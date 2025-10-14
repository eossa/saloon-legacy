<?php

namespace Saloon\Traits\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Auth\QueryAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Auth\DigestAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Auth\CertificateAuthenticator;

trait AuthenticatesRequests
{
    /**
     * The authenticator used in requests.
     *
     * @var Authenticator|null
     */
    protected $authenticator = null;

    /**
     * Default authenticator used.
     *
     * @return Authenticator|null
     */
    protected function defaultAuth()
    {
        return null;
    }

    /**
     * Retrieve the authenticator.
     *
     * @return Authenticator|null
     */
    public function getAuthenticator()
    {
        return isset($this->authenticator) ? $this->authenticator : $this->defaultAuth();
    }

    /**
     * Authenticate the request with an authenticator.
     *
     * @return $this
     */
    public function authenticate(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * Authenticate the request with an Authorization header.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new TokenAuthenticator)` instead.
     *
     * @param string $token
     * @param string $prefix
     *
     * @return $this
     */
    public function withTokenAuth($token, $prefix = 'Bearer')
    {
        return $this->authenticate(new TokenAuthenticator($token, $prefix));
    }

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new BasicAuthenticator)` instead.
     *
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function withBasicAuth($username, $password)
    {
        return $this->authenticate(new BasicAuthenticator($username, $password));
    }

    /**
     * Authenticate the request with "digest" authentication.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new DigestAuthenticator)` instead.
     *
     * @param string $username
     * @param string $password
     * @param string $digest
     *
     * @return $this
     */
    public function withDigestAuth($username, $password, $digest)
    {
        return $this->authenticate(new DigestAuthenticator($username, $password, $digest));
    }

    /**
     * Authenticate the request with a query parameter token.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new QueryAuthenticator)` instead.
     *
     * @param string $parameter
     * @param string $value
     *
     * @return $this
     */
    public function withQueryAuth($parameter, $value)
    {
        return $this->authenticate(new QueryAuthenticator($parameter, $value));
    }

    /**
     * Authenticate the request with a header.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new HeaderAuthenticator)` instead.
     *
     * @param string $accessToken
     * @param string $headerName
     *
     * @return $this
     */
    public function withHeaderAuth($accessToken, $headerName = 'Authorization')
    {
        return $this->authenticate(new HeaderAuthenticator($accessToken, $headerName));
    }

    /**
     * Authenticate the request with a certificate.
     *
     * @deprecated This method will be removed in Saloon v4. You should use the defaultAuth method or the `->authenticate(new CertificateAuthenticator)` instead.
     *
     * @param string $path
     * @param ?string $password
     *
     * @return $this
     */
    public function withCertificateAuth($path, $password = null)
    {
        return $this->authenticate(new CertificateAuthenticator($path, $password));
    }
}
