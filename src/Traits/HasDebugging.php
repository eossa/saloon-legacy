<?php

namespace Saloon\Traits;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Helpers\Debugger;
use Saloon\Http\PendingRequest;

trait HasDebugging
{
    /**
     * Register a request debugger
     *
     * Leave blank for a default debugger (requires symfony/var-dump)
     *
     * @param callable(PendingRequest, RequestInterface): void|null $onRequest
     * @param bool $die
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function debugRequest(callable $onRequest = null, $die = false)
    {
        // When the user has not specified a callable to debug with, we will use this default
        // debugging driver. This will use symfony/var-dumper to display a nice output to
        // the user's screen of the request.

        if (is_null($onRequest)) {
            $onRequest = function (PendingRequest $pendingRequest, RequestInterface $psrRequest) {
                Debugger::symfonyRequestDebugger($pendingRequest, $psrRequest);
            };
        }

        // Register the middleware - we will use PipeOrder::FIRST to ensure that the response
        // is shown before it is modified by the user's middleware.

        $this->middleware()->onRequest(
            static function (PendingRequest $pendingRequest) use ($onRequest, $die) {
                $onRequest($pendingRequest, $pendingRequest->createPsrRequest());

                if ($die) {
                    Debugger::dieApp();
                }
            },
            null,
            PipeOrder::LAST
        );

        return $this;
    }

    /**
     * Register a response debugger
     *
     * Leave blank for a default debugger (requires symfony/var-dump)
     *
     * @param callable(Response, ResponseInterface): void|null $onResponse
     * @param bool $die
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function debugResponse(callable $onResponse = null, $die = false)
    {
        // When the user has not specified a callable to debug with, we will use this default
        // debugging driver. This will use symfony/var-dumper to display a nice output to
        // the user's screen of the response.

        if (is_null($onResponse)) {
            $onResponse = function (Response $response, ResponseInterface $psrResponse) {
                Debugger::symfonyResponseDebugger($response, $psrResponse);
            };
        }

        // Register the middleware - we will use PipeOrder::FIRST to ensure that the response
        // is shown before it is modified by the user's middleware.

        $this->middleware()->onResponse(
            static function (Response $response) use ($onResponse, $die) {
                $onResponse($response, $response->getPsrResponse());

                if ($die) {
                    Debugger::dieApp();
                }
            },
            null,
            PipeOrder::FIRST
        );

        return $this;
    }

    /**
     * Dump a pretty output of the request and response.
     *
     * This is useful if you would like to see the request right before it is sent
     * to inspect the body and URI to ensure it is correct. You can also inspect
     * the raw response as it comes back.
     *
     * Note that any changes made to the PSR request by the sender will not be
     * reflected by this output.
     *
     * Requires symfony/var-dumper
     *
     * @param bool $die
     *
     * @return $this
     * @throws DuplicatePipeNameException
     */
    public function debug($die = false)
    {
        return $this->debugRequest()->debugResponse(null, $die);
    }
}
