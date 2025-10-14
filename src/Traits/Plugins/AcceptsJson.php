<?php

namespace Saloon\Traits\Plugins;

use Saloon\Http\PendingRequest;

trait AcceptsJson
{
    /**
     * Boot AcceptsJson Plugin
     *
     * @return void
     */
    public static function bootAcceptsJson(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Accept', 'application/json');
    }
}
