<?php

namespace Saloon\Traits\RequestProperties;

use Saloon\Http\Request;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

trait HasTries
{
    /**
     * The number of times a request should be retried if a failure response is returned.
     *
     * Set to null to disable the retry functionality.
     *
     * @var ?int
     */
    public $tries = null;

    /**
     * The interval in milliseconds Saloon should wait between retries.
     *
     * For example 500ms = 0.5 seconds.
     *
     * Set to null to disable the retry interval.
     *
     * @var ?int
     */
    public $retryInterval = null;

    /**
     * Should Saloon use exponential backoff during retries?
     *
     * When true, Saloon will double the retry interval after each attempt.
     *
     * @var ?bool
     */
    public $useExponentialBackoff = null;

    /**
     * Should Saloon throw an exception after exhausting the maximum number of retries?
     *
     * When false, Saloon will return the last response attempted.
     *
     * Set to null to always throw after maximum retry attempts.
     *
     * @var ?bool
     */
    public $throwOnMaxTries = null;

    /**
     * Define whether the request should be retried.
     *
     * You can access the response from the RequestException. You can also modify the
     * request before the next attempt is made.
     *
     * @param FatalRequestException|RequestException $exception
     * @param Request $request
     *
     * @return bool
     */
    public function handleRetry($exception, Request $request)
    {
        return true;
    }
}
