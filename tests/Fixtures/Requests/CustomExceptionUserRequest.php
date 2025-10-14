<?php

namespace Saloon\Tests\Fixtures\Requests;

use Exception;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Tests\Fixtures\Exceptions\CustomRequestException;

class CustomExceptionUserRequest extends Request
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
     * Get the custom request exception
     *
     * @param Response $response
     * @param Exception|null $senderException
     *
     * @return Exception|null
     */
    public function getRequestException(Response $response, Exception $senderException = null)
    {
        return new CustomRequestException($response, 'Oh yee-naw.', 0, $senderException);
    }
}
