<?php

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;

class CallableMockResponse
{
    /**
     * @return MockResponse
     */
    public function __invoke(PendingRequest $pendingRequest)
    {
        return new MockResponse(['request_class' => get_class($pendingRequest->getRequest())], 200);
    }
}
