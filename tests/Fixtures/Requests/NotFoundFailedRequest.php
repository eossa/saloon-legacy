<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class NotFoundFailedRequest extends Request
{
    use AlwaysThrowOnErrors;

    /**
     * Define the HTTP method.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/not-found';
    }

    /**
     * Determine if the request has failed
     *
     * @return bool|null
     */
    public function hasRequestFailed(Response $response)
    {
        if ($response->status() === 404) {
            return false;
        }

        return ($response->serverError() || $response->clientError());
    }
}
