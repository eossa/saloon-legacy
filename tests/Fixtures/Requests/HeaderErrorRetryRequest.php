<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;

class HeaderErrorRetryRequest extends RetryUserRequest
{
    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/header-error';
    }
}
