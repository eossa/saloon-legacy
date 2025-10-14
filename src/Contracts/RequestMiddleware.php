<?php

namespace Saloon\Contracts;

use Saloon\Http\PendingRequest;

interface RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @return PendingRequest|FakeResponse|void
     */
    public function __invoke(PendingRequest $pendingRequest);
}
