<?php

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;

class MergeDelay
{
    /**
     * Merge connector and request delay
     *
     * @return PendingRequest
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $pendingRequest->delay()->set($connector->delay()->get());

        if ($request->delay()->isNotEmpty()) {
            $pendingRequest->delay()->set($request->delay()->get());
        }

        return $pendingRequest;
    }
}
