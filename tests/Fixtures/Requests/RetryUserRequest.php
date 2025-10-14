<?php

namespace Saloon\Tests\Fixtures\Requests;

use Closure;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Request as RequestContract;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

class RetryUserRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected $method = Method::GET;

    /**
     * @var Closure|null
     */
    protected $handleRetry;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }

    /**
     * @param int|null $tries
     * @param int $retryInterval
     * @param bool|null $throwOnMaxTries
     * @param Closure|null $handleRetry
     */
    public function __construct($tries = null, $retryInterval = 0, $throwOnMaxTries = null, Closure $handleRetry = null)
    {
        // These are just for us to test the various retries

        $this->tries = $tries;
        $this->retryInterval = $retryInterval;
        $this->throwOnMaxTries = $throwOnMaxTries;
        $this->handleRetry = $handleRetry;
    }

    /**
     * @param FatalRequestException|RequestException $exception
     *
     * @return bool
     */
    public function handleRetry($exception, RequestContract $request)
    {
        return isset($this->handleRetry) ? call_user_func($this->handleRetry, $exception, $request) : true;
    }
}
