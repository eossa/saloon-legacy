<?php

namespace Saloon\Traits;

use Exception;
use Saloon\Http\Response;

trait ManagesExceptions
{
    /**
     * Determine if the request has failed.
     *
     * @return bool|null
     */
    public function hasRequestFailed(Response $response)
    {
        return null;
    }

    /**
     * Get the request exception.
     *
     * @param Response $response
     * @param Exception|null $senderException
     *
     * @return Exception|null
     */
    public function getRequestException(Response $response, Exception $senderException = null)
    {
        return null;
    }

    /**
     * Determine if we should throw an exception if the `$response->throw()` is
     * used, or when the `AlwaysThrowOnErrors` trait is used.
     *
     * @return bool
     */
    public function shouldThrowRequestException(Response $response)
    {
        return $response->failed();
    }
}
