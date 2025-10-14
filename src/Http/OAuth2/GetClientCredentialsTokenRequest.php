<?php

namespace Saloon\Http\OAuth2;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\Plugins\AcceptsJson;

class GetClientCredentialsTokenRequest extends Request implements HasBody
{
    use HasFormBody;
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected $method = Method::POST;

    /**
     * @var OAuthConfig
     */
    protected $oauthConfig;

    /**
     * @var string[]
     */
    protected $scopes;

    /**
     * @var string
     */
    protected $scopeSeparator;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return $this->oauthConfig->getTokenEndpoint();
    }

    /**
     * Requires the authorization code and OAuth 2 config.
     *
     * @param OAuthConfig $oauthConfig
     * @param array<string> $scopes
     * @param string $scopeSeparator
     */
    public function __construct(OAuthConfig $oauthConfig, array $scopes = [], $scopeSeparator = ' ')
    {
        $this->oauthConfig = $oauthConfig;
        $this->scopes = $scopes;
        $this->scopeSeparator = $scopeSeparator;
    }

    /**
     * Register the default data.
     *
     * @return array{
     *     grant_type: string,
     *     client_id: string,
     *     client_secret: string,
     *     scope: string,
     * }
     */
    public function defaultBody()
    {
        return [
            'grant_type' => 'client_credentials',
            'client_id' => $this->oauthConfig->getClientId(),
            'client_secret' => $this->oauthConfig->getClientSecret(),
            'scope' => implode($this->scopeSeparator, array_merge($this->oauthConfig->getDefaultScopes(), $this->scopes)),
        ];
    }
}
