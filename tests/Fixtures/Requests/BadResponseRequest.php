<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class BadResponseRequest extends Request
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
     * Check if we should throw an exception
     *
     * @return bool
     */
    public function shouldThrowRequestException(Response $response)
    {
        return strpos($response->body(), 'Yee-naw:') !== false;
    }
}
