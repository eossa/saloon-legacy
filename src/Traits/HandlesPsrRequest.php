<?php

namespace Saloon\Traits;

use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;

trait HandlesPsrRequest
{
    /**
     * Handle the PSR request before it is sent
     *
     * @return RequestInterface
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest)
    {
        return $request;
    }
}
