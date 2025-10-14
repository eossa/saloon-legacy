<?php

namespace Saloon\Tests\Fixtures\Connectors;

use GuzzleHttp\Psr7\Uri;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;

class ModifiedPsrRequestConnector extends TestConnector
{
    /**
     * @return RequestInterface
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest)
    {
        return $request->withUri(new Uri('https://google.com'));
    }
}
