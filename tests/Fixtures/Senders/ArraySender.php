<?php

namespace Saloon\Tests\Fixtures\Senders;

use Saloon\Contracts\Sender;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Data\FactoryCollection;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Saloon\Http\Response;
use Saloon\Http\Senders\Factories\GuzzleMultipartBodyFactory;

class ArraySender implements Sender
{
    /**
     * Get the factory collection
     *
     * @return FactoryCollection
     */
    public function getFactoryCollection()
    {
        $factory = new HttpFactory();

        return new FactoryCollection(
            $factory,
            $factory,
            $factory,
            $factory,
            new GuzzleMultipartBodyFactory()
        );
    }

    /**
     * Send the request synchronously
     *
     * @return Response
     *
     * @throws InvalidResponseClassException
     */
    public function send(PendingRequest $pendingRequest)
    {
        /** @var class-string<Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        return $responseClass::fromPsrResponse(new GuzzleResponse(200, ['X-Fake' => true], 'Default'), $pendingRequest, $pendingRequest->createPsrRequest());
    }

    /**
     * Send the request asynchronously
     *
     * @param PendingRequest $pendingRequest
     *
     * @return PromiseInterface
     */
    public function sendAsync(PendingRequest $pendingRequest)
    {
        //
    }
}
