<?php

namespace Saloon\Traits\OAuth2;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\OAuth2\GetClientCredentialsTokenRequest;

trait ClientCredentialsGrant
{
    use HasOAuthConfig;

    /**´
     * Get the access token
     *
     * @template TRequest of Request
     *
     * @param array<string> $scopes
     * @param string $scopeSeparator
     * @param bool $returnResponse
     * @param callable(TRequest): (void)|null $requestModifier
     *
     * @return OAuthAuthenticator|Response
     *
     * @return OAuthAuthenticator|Response
     * @throws OAuthConfigValidationException
     * @throws FatalRequestException
     * @throws RequestException
     * @throws PendingRequestException
     * @throws Exception
     */
    public function getAccessToken(array $scopes = [], $scopeSeparator = ' ', $returnResponse = false, callable $requestModifier = null)
    {
        $this->oauthConfig()->validate(false);

        $request = $this->resolveAccessTokenRequest($this->oauthConfig(), $scopes, $scopeSeparator);

        $request = $this->oauthConfig()->invokeRequestModifier($request);

        if (is_callable($requestModifier)) {
            $requestModifier($request);
        }

        $response = $this->send($request);

        if ($returnResponse === true) {
            return $response;
        }

        $response->throwException();

        return $this->createOAuthAuthenticatorFromResponse($response);
    }

    /**
     * Create the OAuthAuthenticator from a response.
     *
     * @return OAuthAuthenticator
     *
     * @throws Exception
     */
    protected function createOAuthAuthenticatorFromResponse(Response $response)
    {
        $responseData = $response->object();

        $accessToken = $responseData->access_token;
        $expiresAt = null;

        if (isset($responseData->expires_in) && is_numeric($responseData->expires_in)) {
            $expiresAt = (new DateTimeImmutable)->add(
                DateInterval::createFromDateString((int)$responseData->expires_in . ' seconds')
            );
        }

        return $this->createOAuthAuthenticator($accessToken, $expiresAt);
    }

    /**
     * Create the authenticator.
     *
     * @param string $accessToken
     * @param DateTimeImmutable|null $expiresAt
     *
     * @return OAuthAuthenticator
     */
    protected function createOAuthAuthenticator($accessToken, DateTimeImmutable $expiresAt = null)
    {
        return new AccessTokenAuthenticator($accessToken, null, $expiresAt);
    }

    /**
     * Resolve the access token request
     *
     * @param OAuthConfig $oauthConfig
     * @param array $scopes
     * @param string $scopeSeparator
     *
     * @return Request
     */
    protected function resolveAccessTokenRequest(OAuthConfig $oauthConfig, array $scopes = [], $scopeSeparator = ' ')
    {
        return new GetClientCredentialsTokenRequest($oauthConfig, $scopes, $scopeSeparator);
    }
}
