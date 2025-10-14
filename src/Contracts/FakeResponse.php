<?php

namespace Saloon\Contracts;

use Closure;
use Exception;
use Saloon\Http\Faking\Fixture;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

interface FakeResponse
{
    /**
     * Get the status from the responses
     *
     * @return int
     */
    public function status();

    /**
     * Get the headers
     *
     * @return ArrayStore
     */
    public function headers();

    /**
     * Get the response body
     *
     * @return BodyRepository
     */
    public function body();

    /**
     * Throw an exception on the request.
     *
     * @param Closure|Exception $value
     *
     * @return $this
     */
    public function throwException($value);

    /**
     * Get the exception
     *
     * @return Exception|null
     */
    public function getException(PendingRequest $pendingRequest);

    /**
     * Create a new mock response from a fixture
     *
     * @param string $name
     *
     * @return Fixture
     */
    public static function fixture($name);

    /**
     * Get the response as a ResponseInterface
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     *
     * @return ResponseInterface
     */
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory);
}
