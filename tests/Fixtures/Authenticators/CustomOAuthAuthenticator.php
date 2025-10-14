<?php

namespace Saloon\Tests\Fixtures\Authenticators;

use DateTimeImmutable;
use Saloon\Http\Auth\AccessTokenAuthenticator;

class CustomOAuthAuthenticator extends AccessTokenAuthenticator
{
    /**
     * @var string
     */
    public $greeting;

    /**
     * Constructor
     *
     * @param string $accessToken
     * @param string $greeting
     * @param string|null $refreshToken
     * @param DateTimeImmutable|null $expiresAt
     */
    public function __construct(
        $accessToken,
        $greeting,
        $refreshToken = null,
        DateTimeImmutable $expiresAt = null
    ) {
        $this->accessToken = $accessToken;
        $this->greeting = $greeting;
        $this->refreshToken = $refreshToken;
        $this->expiresAt = $expiresAt;
    }


    /**
     * @return string
     */
    public function getGreeting()
    {
        return $this->greeting;
    }
}
