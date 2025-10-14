<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;

class ModifiedPsrUserRequest extends UserRequest
{
    /**
     * @return RequestInterface
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest)
    {
        return $request->withHeader('X-Howdy', 'Yeehaw');
    }
}
