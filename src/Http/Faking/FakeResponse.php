<?php

namespace Saloon\Http\Faking;

use Closure;
use Exception;
use Saloon\Exceptions\DirectoryNotFoundException;
use Saloon\Exceptions\UnableToCreateDirectoryException;
use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;
use Saloon\Contracts\FakeResponse as FakeResponseContract;

/**
 * @method static static make(mixed $body = [], int $status = 200, array $headers = [])
 */
class FakeResponse implements FakeResponseContract
{
    use Makeable;

    /**
     * HTTP Status Code
     *
     * @var int
     */
    protected $status;

    /**
     * Headers
     *
     * @var ArrayStoreContract
     */
    protected $headers;

    /**
     * Request Body
     *
     * @var BodyRepository
     */
    protected $body;

    /**
     * Exception Closure
     *
     * @var Closure|null
     */
    protected $responseException = null;

    /**
     * Create a new mock response
     *
     * @param array<string, mixed>|string $body
     * @param int $status
     * @param array<string, mixed> $headers
     */
    public function __construct($body = [], $status = 200, array $headers = [])
    {
        $this->body = is_array($body) ? new JsonBodyRepository($body) : new StringBodyRepository($body);
        $this->status = $status;
        $this->headers = new ArrayStore($headers);
    }

    /**
     *  Get the response body
     *
     * @return BodyRepository
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Get the status from the responses
     *
     * @return int
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get the headers
     *
     * @return ArrayStoreContract
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Throw an exception on the request.
     *
     * @param Closure|Exception $value
     *
     * @return $this
     */
    public function throwException($value)
    {
        $closure = $value instanceof Exception
            ? static function () use ($value) {
                return $value;
            }
            : $value;

        $this->responseException = $closure;

        return $this;
    }

    /**
     * Invoke the exception.
     *
     * @return Exception|null
     */
    public function getException(PendingRequest $pendingRequest)
    {
        if (! $this->responseException instanceof Closure) {
            return null;
        }

        return call_user_func($this->responseException, $pendingRequest);
    }

    /**
     * Create a new mock response from a fixture
     *
     * @param string $name
     *
     * @return Fixture
     *
     * @throws DirectoryNotFoundException
     * @throws UnableToCreateDirectoryException
     */
    public static function fixture($name)
    {
        return new Fixture($name);
    }

    /**
     * Get the response as a ResponseInterface
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @return ResponseInterface
     */
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory)
    {
        $response = $responseFactory->createResponse($this->status());

        foreach ($this->headers()->all() as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }

        return $response->withBody($this->body()->toStream($streamFactory));
    }
}
