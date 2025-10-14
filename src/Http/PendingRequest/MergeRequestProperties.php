<?php

namespace Saloon\Http\PendingRequest;

use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Http\PendingRequest;

class MergeRequestProperties
{
    /**
     * Merge connector and request properties (headers, query, config, middleware)
     *
     * @return PendingRequest
     *
     * @throws DuplicatePipeNameException
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        $connector = $pendingRequest->getConnector();
        $request = $pendingRequest->getRequest();

        $pendingRequest->headers()->merge(
            $connector->headers()->all(),
            $request->headers()->all()
        );

        $pendingRequest->query()->merge(
            $connector->query()->all(),
            $request->query()->all()
        );

        $pendingRequest->config()->merge(
            $connector->config()->all(),
            $request->config()->all()
        );

        $pendingRequest->middleware()
            ->merge($connector->middleware())
            ->merge($request->middleware(), true);

        return $pendingRequest;
    }
}
