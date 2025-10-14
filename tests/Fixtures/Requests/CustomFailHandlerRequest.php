<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class CustomFailHandlerRequest extends Request
{
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
        return '/user';
    }

    /**
     * Determine if the request has failed
     *
     * @param Response $response
     *
     * @return bool
     */
    public function hasRequestFailed(Response $response)
    {
        return strpos($response->body(), 'Yee-naw:') !== false;
    }
}
