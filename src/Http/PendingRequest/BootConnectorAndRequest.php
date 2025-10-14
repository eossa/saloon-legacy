<?php

namespace Saloon\Http\PendingRequest;

use Saloon\Http\PendingRequest;

class BootConnectorAndRequest
{
    /**
     * Boot the connector and request
     *
     * @return PendingRequest
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        $pendingRequest->getConnector()->boot($pendingRequest);
        $pendingRequest->getRequest()->boot($pendingRequest);

        return $pendingRequest;
    }
}
