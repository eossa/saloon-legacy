<?php

namespace Saloon\Traits\OAuth2;

use Saloon\Helpers\OAuth2\OAuthConfig;

trait HasOAuthConfig
{
    /**
     * The OAuth2 Config
     *
     * @var OAuthConfig
     */
    protected $oauthConfig;

    /**
     * Manage the OAuth2 config
     *
     * @return OAuthConfig
     */
    public function oauthConfig()
    {
        if (isset($this->oauthConfig)) {
            return $this->oauthConfig;
        }
        return $this->oauthConfig = $this->defaultOauthConfig();
    }

    /**
     * Define the default Oauth 2 Config.
     *
     * @return OAuthConfig
     */
    protected function defaultOauthConfig()
    {
        return OAuthConfig::make();
    }
}
