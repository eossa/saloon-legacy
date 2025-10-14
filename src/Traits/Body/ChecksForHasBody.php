<?php

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Exceptions\BodyException;

trait ChecksForHasBody
{
    /**
     * Check if the request or connector has the WithBody class.
     *
     * @return void
     *
     * @throws BodyException
     */
    public function bootChecksForHasBody(PendingRequest $pendingRequest)
    {
        if ($pendingRequest->getRequest() instanceof HasBody || $pendingRequest->getConnector() instanceof HasBody) {
            return;
        }

        throw new BodyException(sprintf('You have added a body trait without implementing `%s` on your request or connector.', HasBody::class));
    }
}
