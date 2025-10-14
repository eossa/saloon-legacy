<?php

namespace Saloon\Http;

use Closure;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\InvalidPoolItemException;
use Traversable;

class Pool
{
    /**
     * Requests inside the pool
     *
     * @var iterable<PromiseInterface|Request>
     */
    protected $requests;

    /**
     * Handle Response Callback
     *
     * @var Closure(Response, array-key, PromiseInterface): (void)|null
     */
    protected $responseHandler = null;

    /**
     * Handle Exception Callback
     *
     * @var Closure(mixed, array-key, PromiseInterface): (void)|null
     */
    protected $exceptionHandler = null;

    /**
     * Connector
     *
     * @var Connector
     */
    protected $connector;

    /**
     * Concurrency
     *
     * How many requests will be sent at once.
     *
     * @var int|Closure(int): int
     */
    protected $concurrency;

    /**
     * Constructor
     *
     * @param Connector $connector
     * @param callable|iterable $requests
     * @param callable|int $concurrency
     * @param callable(Response, array-key $key, PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function __construct(Connector $connector, $requests = [], $concurrency = 5, callable $responseHandler = null, callable $exceptionHandler = null)
    {
        $this->connector = $connector;
        $this->setRequests($requests);
        $this->setConcurrency($concurrency);

        if (! is_null($responseHandler)) {
            $this->withResponseHandler($responseHandler);
        }

        if (! is_null($exceptionHandler)) {
            $this->withExceptionHandler($exceptionHandler);
        }
    }

    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable(Response, array-key $key, PromiseInterface $poolAggregate): (void) $callable
     * @return $this
     */
    public function withResponseHandler(callable $callable)
    {
        $this->responseHandler = $callable;

        return $this;
    }

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable(mixed $reason, array-key $key, PromiseInterface $poolAggregate): (void) $callable
     *
     * @return $this
     */
    public function withExceptionHandler(callable $callable)
    {
        $this->exceptionHandler = $callable;

        return $this;
    }

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int|callable(int $pendingRequests): (int) $concurrency
     *
     * @return $this
     */
    public function setConcurrency($concurrency)
    {
        $this->concurrency = is_callable($concurrency)
            ? function ($pendingRequests) use ($concurrency) {
                return $concurrency($pendingRequests);
            }
            : $concurrency;

        return $this;
    }

    /**
     * Set the requests
     *
     * @param iterable<PromiseInterface|Request>|callable(Connector): iterable<PromiseInterface|Request> $requests
     *
     * @return $this
     */
    public function setRequests($requests)
    {
        if (is_callable($requests)) {
            $requests = $requests($this->connector);
        }

        if (is_array($requests) || $requests instanceof Traversable) {
            $requests = function () use ($requests) {
                foreach ($requests as $key => $value) {
                    yield $key => $value;
                }
            };
        }

        $this->requests = $requests();

        return $this;
    }

    /**
     * Get the request generator
     *
     * @return iterable<PromiseInterface|Request>
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Send the pool and create a Promise
     *
     * @return PromiseInterface
     *
     * @throws InvalidPoolItemException
     */
    public function send()
    {
        // Iterate through the existing generator and "prepare" the requests.
        // If they are SaloonRequests then we should convert them into
        // promises.

        $preparedRequests = function () {
            foreach ($this->requests as $key => $request) {
                switch (true) {
                    case $request instanceof Request:
                        yield $key => $this->connector->sendAsync($request);
                        break;
                    case $request instanceof PromiseInterface:
                        yield $key => $request;
                        break;
                    default:
                        throw new InvalidPoolItemException;
                }
            }
        };

        // Next we'll use an EachPromise which accepts an iterator of
        // requests and will process them as the concurrency we set.

        $eachPromise = new EachPromise($preparedRequests(), [
            'concurrency' => $this->concurrency,
            'fulfilled' => $this->responseHandler,
            'rejected' => $this->exceptionHandler,
        ]);

        return $eachPromise->promise();
    }
}
