<?php

namespace Saloon\Traits\Connector;

use Exception;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\FakeResponse;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\PendingRequestException;

trait ManagesFakeResponses
{
    /**
     * Create the fake response
     *
     * @return Response|PromiseInterface
     *
     * @throws PendingRequestException
     * @throws Exception
     */
    protected function createFakeResponse(PendingRequest $pendingRequest)
    {
        $fakeResponse = $pendingRequest->getFakeResponse();

        if (! $fakeResponse instanceof FakeResponse) {
            throw new PendingRequestException('Unable to create fake response because there is no fake response data.');
        }

        $isAsynchronous = $pendingRequest->isAsynchronous();

        // Check if the FakeResponse throws an exception. If the request is
        // asynchronous, then we should allow the promise handler to deal with the exception.

        $exception = $fakeResponse->getException($pendingRequest);

        if ($exception instanceof Exception && $isAsynchronous === false) {
            throw $exception;
        }

        // Let's create our response!

        $factories = $pendingRequest->getFactoryCollection();

        $response = $fakeResponse->createPsrResponse(
            $factories->responseFactory,
            $factories->streamFactory
        );

        /** @var class-string<Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        $response = $responseClass::fromPsrResponse(
            $response,
            $pendingRequest,
            $pendingRequest->createPsrRequest(),
            $exception
        );

        $response->setFakeResponse($fakeResponse);

        // When the FakeResponse is specifically a MockResponse then we will
        // record the response, and we'll set the "isMocked" property on
        // the response.

        if ($fakeResponse instanceof MockResponse) {
            $mockClient = $pendingRequest->getMockClient();
            if ($mockClient) {
                $mockClient->recordResponse($response);
            }

            $response->setMocked(true);
        }

        // When the request isn't async we'll just return the response

        if ($isAsynchronous === false) {
            return $response;
        }

        // When mocking asynchronous requests we need to wrap the response
        // in FulfilledPromise or RejectedPromise depending on if the
        // response has an exception.

        if (is_null($exception)) {
            $exception = $response->toException();
        }

        return is_null($exception) ? new FulfilledPromise($response) : new RejectedPromise($exception);
    }
}
