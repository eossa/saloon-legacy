<?php

namespace Saloon\Contracts;

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Data\FactoryCollection;
use GuzzleHttp\Promise\PromiseInterface;

interface Sender
{
    /**
     * Get the factory collection
     *
     * @return FactoryCollection
     */
    public function getFactoryCollection();

    /**
     * Send the request synchronously
     *
     * @return Response
     */
    public function send(PendingRequest $pendingRequest);

    /**
     * Send the request asynchronously
     *
     * @return PromiseInterface
     */
    public function sendAsync(PendingRequest $pendingRequest);
}
