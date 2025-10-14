<?php

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;

trait HasXmlBody
{
    use HasStringBody;

    /**
     * Boot the plugin
     *
     * @return void
     */
    public function bootHasXmlBody(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('Content-Type', 'application/xml');
    }
}
