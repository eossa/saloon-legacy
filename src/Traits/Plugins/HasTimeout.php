<?php

namespace Saloon\Traits\Plugins;

use Saloon\Config;
use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;

trait HasTimeout
{
    /**
     * Boot HasTimeout plugin.
     *
     * @return void
     */
    public function bootHasTimeout(PendingRequest $pendingRequest)
    {
        $pendingRequest->config()->merge([
            RequestOptions::CONNECT_TIMEOUT => $this->getConnectTimeout(),
            RequestOptions::TIMEOUT => $this->getRequestTimeout(),
        ]);
    }

    /**
     * Get the request connection timeout.
     *
     * @return float
     */
    public function getConnectTimeout()
    {
        return property_exists($this, 'connectTimeout') ? $this->connectTimeout : Config::$defaultConnectionTimeout;
    }

    /**
     * Get the request timeout.
     *
     * @return float
     */
    public function getRequestTimeout()
    {
        return property_exists($this, 'requestTimeout') ? $this->requestTimeout : Config::$defaultRequestTimeout;
    }
}
