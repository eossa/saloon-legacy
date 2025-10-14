<?php

namespace Saloon\Http\Auth;

use DateTimeImmutable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\OAuthAuthenticator;

class AccessTokenAuthenticator implements OAuthAuthenticator
{
    /**
     * @var string
     */
    public $accessToken;

    /**
     * @var string|null
     */
    public $refreshToken;

    /**
     * @var DateTimeImmutable|null
     */
    public $expiresAt;

    /**
     * Constructor
     *
     * @param string $accessToken
     * @param string|null $refreshToken
     * @param DateTimeImmutable|null $expiresAt
     */
    public function __construct(
        $accessToken,
        $refreshToken = null,
        DateTimeImmutable $expiresAt = null
    ) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = $expiresAt;
    }

    /**
     * Apply the authentication to the request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Authorization', 'Bearer ' . $this->getAccessToken());
    }

    /**
     * Check if the access token has expired.
     *
     * @return bool
     */
    public function hasExpired()
    {
        if (is_null($this->expiresAt)) {
            return false;
        }

        return $this->expiresAt->getTimestamp() <= (new DateTimeImmutable)->getTimestamp();
    }

    /**
     * Check if the access token has not expired.
     *
     * @return bool
     */
    public function hasNotExpired()
    {
        return ! $this->hasExpired();
    }

    /**
     * Get the access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the refresh token
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get the expires at DateTime instance
     *
     * @return DateTimeImmutable|null
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Check if the authenticator is refreshable
     *
     * @return bool
     */
    public function isRefreshable()
    {
        return isset($this->refreshToken);
    }

    /**
     * Check if the authenticator is not refreshable
     *
     * @return bool
     */
    public function isNotRefreshable()
    {
        return ! $this->isRefreshable();
    }

    /**
     * Serialize the access token.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this);
    }

    /**
     * Unserialize the access token.
     *
     * @param string $string
     *
     * @return $this
     */
    public static function unserialize($string)
    {
        return unserialize($string);
    }
}
