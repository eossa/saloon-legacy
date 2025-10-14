<?php

namespace Saloon\Traits\Plugins;

use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;

trait AlwaysThrowOnErrors
{
    /**
     * Boot AlwaysThrowOnErrors Plugin
     *
     * @return void
     *
     * @throws DuplicatePipeNameException
     */
    public static function bootAlwaysThrowOnErrors(PendingRequest $pendingRequest)
    {
        // This middleware will simply use the "throw" method on the response
        // which will check if the connector/request deems the response as a
        // failure - if it does, it will throw a RequestException.

        $pendingRequest->middleware()->onResponse(
            static function (Response $response) {
                return $response->throwException();
            },
            'alwaysThrowOnErrors',
            PipeOrder::LAST
        );
    }
}
