<?php

namespace Saloon\Tests\Unit\Oauth2;

use PHPUnit\Framework\TestCase;
use Saloon\Tests\Helpers\Date;
use Saloon\Http\Auth\AccessTokenAuthenticator;

class AccessTokenAuthenticatorTest extends TestCase
{
    public function testCanBeSerializedAndUnserialized()
    {
        $accessToken = 'access';
        $refreshToken = 'refresh';
        $expiresAt = Date::now()->toDateTime();

        $authenticator = new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);

        $this->assertEquals($accessToken, $authenticator->getAccessToken());
        $this->assertEquals($refreshToken, $authenticator->getRefreshToken());
        $this->assertEquals($expiresAt, $authenticator->getExpiresAt());

        $serialized = $authenticator->serialize();

        $this->assertInternalType('string', $serialized);

        $unserialized = AccessTokenAuthenticator::unserialize($serialized);

        $this->assertEquals($authenticator, $unserialized);
    }

    public function testCanReturnIfItHasExpiredOrNot()
    {
        $accessToken = 'access';
        $refreshToken = 'refresh';
        $expiresAt = Date::now()->subMinutes(5)->toDateTime();

        $authenticator = new AccessTokenAuthenticator($accessToken, $refreshToken, $expiresAt);

        $this->assertTrue($authenticator->isRefreshable());
        $this->assertFalse($authenticator->isNotRefreshable());
        $this->assertTrue($authenticator->hasExpired());
        $this->assertFalse($authenticator->hasNotExpired());
    }

    public function testCanBeConstructedWithoutARefreshTokenOrExpiry()
    {
        $authenticator = new AccessTokenAuthenticator('access');

        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertNull($authenticator->getRefreshToken());
        $this->assertNull($authenticator->getExpiresAt());
        $this->assertFalse($authenticator->isRefreshable());
        $this->assertTrue($authenticator->isNotRefreshable());
    }

    public function testCanBeConstructedWithJustAnAccessTokenAndExpiry()
    {
        $expiresAt = Date::now()->subMinutes(5)->toDateTime();

        $authenticator = new AccessTokenAuthenticator('access', null, $expiresAt);

        $this->assertTrue($authenticator->hasExpired());
        $this->assertFalse($authenticator->hasNotExpired());
    }

    public function testItAllowsExpiresInToBeOptional()
    {
        $authenticator = new AccessTokenAuthenticator('access', 'refresh', null);

        $this->assertNull($authenticator->getExpiresAt());
        $this->assertTrue($authenticator->isRefreshable());
        $this->assertFalse($authenticator->isNotRefreshable());
    }
}
