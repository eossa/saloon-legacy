<?php

namespace Saloon\Http\OAuth2;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\Plugins\AcceptsJson;

class GetAccessTokenRequest extends Request implements HasBody
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
     * @var string
     */
    protected $code;

    /**
     * @var OAuthConfig
     */
    protected $oauthConfig;

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
     * @param string $code
     * @param OAuthConfig $oauthConfig
     */
    public function __construct($code, OAuthConfig $oauthConfig)
    {
        $this->code = $code;
        $this->oauthConfig = $oauthConfig;
    }

    /**
     * Register the default data.
     *
     * @return array{
     *     grant_type: string,
     *     code: string,
     *     client_id: string,
     *     client_secret: string,
     *     redirect_uri: string,
     * }
     */
    public function defaultBody()
    {
        return [
            'grant_type' => 'authorization_code',
            'code' => $this->code,
            'client_id' => $this->oauthConfig->getClientId(),
            'client_secret' => $this->oauthConfig->getClientSecret(),
            'redirect_uri' => $this->oauthConfig->getRedirectUri(),
        ];
    }
}
