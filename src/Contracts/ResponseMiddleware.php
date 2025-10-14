<?php

namespace Saloon\Contracts;

use Saloon\Http\Response;

interface ResponseMiddleware
{
    /**
     * Register a response middleware
     *
     * @return Response|void
     */
    public function __invoke(Response $response);
}
