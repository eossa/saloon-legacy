<?php

namespace Saloon\Contracts;

use DateTimeImmutable;

interface OAuthAuthenticator extends Authenticator
{
    /**
     * Get the access token
     *
     * @return string
     */
    public function getAccessToken();

    /**
     * Get the refresh token
     *
     * @return string|null
     */
    public function getRefreshToken();

    /**
     * Get the expiry
     *
     * @return DateTimeImmutable|null
     */
    public function getExpiresAt();

    /**
     * Check if the authenticator has expired
     *
     * @return bool
     */
    public function hasExpired();

    /**
     * Check if the authenticator has not expired
     *
     * @return bool
     */
    public function hasNotExpired();

    /**
     * Check if the authenticator is refreshable
     *
     * @return bool
     */
    public function isRefreshable();

    /**
     * Check if the authenticator is not refreshable
     *
     * @return bool
     */
    public function isNotRefreshable();
}
