<?php

namespace Saloon\Http\Middleware;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\RequestMiddleware;

class DelayMiddleware implements RequestMiddleware
{
    /**
     * Register a request middleware
     *
     * @return void
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        $pendingRequestDelay = $pendingRequest->delay()->get();
        $delay = isset($pendingRequestDelay) ? $pendingRequestDelay : 0;

        usleep($delay * 1000);
    }
}
