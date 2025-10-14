<?php

namespace Saloon\Helpers;

use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\FakeResponse;
use Saloon\Exceptions\Request\FatalRequestException;

class MiddlewarePipeline
{
    /**
     * Request Pipeline
     *
     * @var Pipeline
     */
    protected $requestPipeline;

    /**
     * Response Pipeline
     *
     * @var Pipeline
     */
    protected $responsePipeline;

    /**
     * Fatal Pipeline
     *
     * @var Pipeline
     */
    protected $fatalPipeline;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requestPipeline = new Pipeline();
        $this->responsePipeline = new Pipeline();
        $this->fatalPipeline = new Pipeline();
    }

    /**
     * Add a middleware before the request is sent
     *
     * @param callable(PendingRequest): (PendingRequest|FakeResponse|void) $callable
     * @param ?string $name
     * @param ?string $order
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function onRequest(callable $callable, $name = null, $order = null)
    {
        /**
         * For some reason, PHP is not destructing non-static Closures, or 'things' using non-static Closures, correctly, keeping unused objects intact.
         * Using a *static* Closure, or re-binding it to an empty, anonymous class/object is a workaround for the issue.
         * If we don't, things using the MiddlewarePipeline, in turn, won't destruct.
         * Concretely speaking, for Saloon, this means that the Connector will *not* get destructed, and thereby also not the underlying client.
         * Which in turn leaves open file handles until the process terminates.
         *
         * Do note that this is entirely about our *wrapping* Closure below.
         * The provided callable doesn't affect the MiddlewarePipeline.
         */
        $this->requestPipeline->pipe(static function (PendingRequest $pendingRequest) use ($callable) {
            $result = $callable($pendingRequest);

            if ($result instanceof PendingRequest) {
                return $result;
            }

            if ($result instanceof FakeResponse) {
                $pendingRequest->setFakeResponse($result);
            }

            return $pendingRequest;
        }, $name, $order);

        return $this;
    }

    /**
     * Add a middleware after the request is sent
     *
     * @param callable(Response): (Response|void) $callable
     * @param ?string $name
     * @param ?string $order
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function onResponse(callable $callable, $name = null, $order = null)
    {
        /**
         * For some reason, PHP is not destructing non-static Closures, or 'things' using non-static Closures, correctly, keeping unused objects intact.
         * Using a *static* Closure, or re-binding it to an empty, anonymous class/object is a workaround for the issue.
         * If we don't, things using the MiddlewarePipeline, in turn, won't destruct.
         * Concretely speaking, for Saloon, this means that the Connector will *not* get destructed, and thereby also not the underlying client.
         * Which in turn leaves open file handles until the process terminates.
         *
         * Do note that this is entirely about our *wrapping* Closure below.
         * The provided callable doesn't affect the MiddlewarePipeline.
         */
        $this->responsePipeline->pipe(static function (Response $response) use ($callable) {
            $result = $callable($response);

            return $result instanceof Response ? $result : $response;
        }, $name, $order);

        return $this;
    }

    /**
     * Add a middleware to run on fatal errors
     *
     * @param callable(FatalRequestException): (void) $callable
     * @param ?string $name
     * @param ?string $order
     *
     * @return $this
     *
     * @throws DuplicatePipeNameException
     */
    public function onFatalException(callable $callable, $name = null, $order = null)
    {
        /**
         * For some reason, PHP is not destructing non-static Closures, or 'things' using non-static Closures, correctly, keeping unused objects intact.
         * Using a *static* Closure, or re-binding it to an empty, anonymous class/object is a workaround for the issue.
         * If we don't, things using the MiddlewarePipeline, in turn, won't destruct.
         * Concretely speaking, for Saloon, this means that the Connector will *not* get destructed, and thereby also not the underlying client.
         * Which in turn leaves open file handles until the process terminates.
         *
         * Do note that this is entirely about our *wrapping* Closure below.
         * The provided callable doesn't affect the MiddlewarePipeline.
         */
        $this->fatalPipeline->pipe(static function (FatalRequestException $throwable) use ($callable) {
            $callable($throwable);

            return $throwable;
        }, $name, $order);

        return $this;
    }

    /**
     * Process the request pipeline.
     *
     * @return PendingRequest
     */
    public function executeRequestPipeline(PendingRequest $pendingRequest)
    {
        return $this->requestPipeline->process($pendingRequest);
    }

    /**
     * Process the response pipeline.
     *
     * @return Response
     */
    public function executeResponsePipeline(Response $response)
    {
        return $this->responsePipeline->process($response);
    }

    /**
     * Process the fatal pipeline.
     *
     * @return void
     */
    public function executeFatalPipeline(FatalRequestException $throwable)
    {
        $this->fatalPipeline->process($throwable);
    }

    /**
     * Merge in another middleware pipeline.
     *
     * @return $this
     * @throws DuplicatePipeNameException
     */
    public function merge(MiddlewarePipeline $middlewarePipeline, $debug = false)
    {
        $requestPipes = array_merge(
            $this->getRequestPipeline()->getPipes(),
            $middlewarePipeline->getRequestPipeline()->getPipes()
        );

        $responsePipes = array_merge(
            $this->getResponsePipeline()->getPipes(),
            $middlewarePipeline->getResponsePipeline()->getPipes()
        );

        $fatalPipes = array_merge(
            $this->getFatalPipeline()->getPipes(),
            $middlewarePipeline->getFatalPipeline()->getPipes()
        );

        $this->requestPipeline->setPipes($requestPipes);
        $this->responsePipeline->setPipes($responsePipes);
        $this->fatalPipeline->setPipes($fatalPipes);

        return $this;
    }

    /**
     * Get the request pipeline
     *
     * @return Pipeline
     */
    public function getRequestPipeline()
    {
        return $this->requestPipeline;
    }

    /**
     * Get the response pipeline
     *
     * @return Pipeline
     */
    public function getResponsePipeline()
    {
        return $this->responsePipeline;
    }

    /**
     * Get the fatal pipeline
     *
     * @return Pipeline
     */
    public function getFatalPipeline()
    {
        return $this->fatalPipeline;
    }
}
