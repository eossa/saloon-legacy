<?php

namespace Saloon\Traits\Connector;

use Exception;
use LogicException;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Http\Connector;
use Saloon\Http\Pool;
use Saloon\Http\Request;
use Saloon\Http\Response;
use GuzzleHttp\Promise\Utils;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

trait SendsRequests
{
    use HasSender;
    use ManagesFakeResponses;

    /**
     * Send a request synchronously
     *
     * @param MockClient|null $mockClient
     * @param callable(Exception, Request): (bool)|null $handleRetry
     *
     * @return Response
     *
     * @throws FatalRequestException
     * @throws RequestException
     * @throws PendingRequestException
     * @throws Exception
     */
    public function send(Request $request, MockClient $mockClient = null, callable $handleRetry = null)
    {
        if (is_null($handleRetry)) {
            $handleRetry = static function () {
                return true;
            };
        }

        $attempts = 0;

        $maxTries = isset($request->tries) ? $request->tries : (isset($this->tries) ? $this->tries : 1);
        $retryInterval = isset($request->retryInterval) ? $request->retryInterval : (isset($this->retryInterval) ? $this->retryInterval : 0);
        $throwOnMaxTries = isset($request->throwOnMaxTries) ? $request->throwOnMaxTries : (isset($this->throwOnMaxTries) ? $this->throwOnMaxTries : true);
        $useExponentialBackoff = isset($request->useExponentialBackoff) ? $request->useExponentialBackoff : (isset($this->useExponentialBackoff) ? $this->useExponentialBackoff : false);

        if ($maxTries <= 0) {
            $maxTries = 1;
        }

        if ($retryInterval <= 0) {
            $retryInterval = 0;
        }

        while ($attempts < $maxTries) {
            $attempts++;

            // When the current attempt is greater than one, we will wait
            // the interval (if it has been provided)

            if ($attempts > 1) {
                $sleepTime = $useExponentialBackoff
                    ? $retryInterval * (2 ** ($attempts - 2)) * 1000
                    : $retryInterval * 1000;

                usleep($sleepTime);
            }

            try {
                $pendingRequest = $this->createPendingRequest($request, $mockClient);

                // 🚀 ... 🪐  ... 💫

                if ($pendingRequest->hasFakeResponse()) {
                    $response = $this->createFakeResponse($pendingRequest);
                } else {
                    $response = $this->sender()->send($pendingRequest);
                }

                // We'll execute the response pipeline now so that all the response
                // middleware can be run before we throw any exceptions.

                $response = $pendingRequest->executeResponsePipeline($response);

                // We'll check if our tries is greater than one. If it is, then we will
                // force an exception to be thrown if the request was unsuccessful.
                // This will then force our catch handler to retry the request.

                if ($maxTries > 1) {
                    $response->throwException();
                }

                return $response;
            } catch (FatalRequestException $exception) {
                $resultResponse = $this->handleExceptionOnSend(
                    $attempts,
                    $maxTries,
                    $throwOnMaxTries,
                    $exception,
                    $handleRetry,
                    $request
                );
                if (!is_null($resultResponse)) {
                    return $resultResponse;
                }
            } catch (RequestException $exception) {
                $resultResponse = $this->handleExceptionOnSend(
                    $attempts,
                    $maxTries,
                    $throwOnMaxTries,
                    $exception,
                    $handleRetry,
                    $request
                );
                if (!is_null($resultResponse)) {
                    return $resultResponse;
                }
            }
        }

        throw new LogicException('The request was not sent.');
    }

    /**
     * @param int $attempts
     * @param ?int $maxTries
     * @param ?bool $throwOnMaxTries
     * @param FatalRequestException|RequestException $exception
     * @param callable(Exception, Request): (bool)|null $handleRetry
     * @param Request $request
     *
     * @return Response|void
     *
     * @throws FatalRequestException
     * @throws RequestException
     */
    private function handleExceptionOnSend($attempts, $maxTries, $throwOnMaxTries, $exception, $handleRetry, Request $request)
    {
        // We'll attempt to get the response from the exception. We'll only be able
        // to do this if the exception was a "RequestException".
        $exceptionResponse = $exception instanceof RequestException ? $exception->getResponse() : null;

        // If the exception is a FatalRequestException, we'll execute the fatal pipeline
        if ($exception instanceof FatalRequestException) {
            $exception->getPendingRequest()->executeFatalPipeline($exception);
        }

        // If we've reached our max attempts - we won't try again, but we'll either
        // return the last response made or just throw an exception.

        if ($attempts === $maxTries) {
            if (isset($exceptionResponse) && $throwOnMaxTries === false) {
                return $exceptionResponse;
            }
            throw $exception;
        }

        // Now we'll run the "handleRetry" method on both the connector and the request.
        // This method will return a boolean. If just one of the objects returns false
        // then we won't handle the retry.

        $allowRetry = $handleRetry($exception, $request)
            && $request->handleRetry($exception, $request)
            && $this->handleRetry($exception, $request);

        // If we cannot retry we will simply return the response or throw the exception.

        if ($allowRetry === false) {
            if (isset($exceptionResponse) && $throwOnMaxTries === false) {
                return $exceptionResponse;
            }
            throw $exception;
        }
    }

    /**
     * Send a request asynchronously
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     *
     * @return PromiseInterface
     */
    public function sendAsync(Request $request, MockClient $mockClient = null)
    {
        $sender = $this->sender();

        // We'll wrap the following logic in our own Promise which means we won't
        // build up our PendingRequest until the promise is actually being sent
        // this is great because our middleware will only run right before
        // the request is sent.

        return Utils::task(function () use ($request, $mockClient, $sender) {
            $pendingRequest = $this->createPendingRequest($request, $mockClient)->setAsynchronous(true);

            // We need to check if the Pending Request contains a fake response.
            // If it does, then we will create the fake response. Otherwise,
            // we'll send the request.

            // 🚀 ... 🪐  ... 💫

            if ($pendingRequest->hasFakeResponse()) {
                $requestPromise = $this->createFakeResponse($pendingRequest);
            } else {
                $requestPromise = $sender->sendAsync($pendingRequest);
            }

            $requestPromise->then(function (Response $response) use ($pendingRequest) {
                return $pendingRequest->executeResponsePipeline($response);
            });

            return $requestPromise;
        });
    }

    /**
     * Send a synchronous request and retry if it fails
     *
     * @param int $tries
     * @param int $interval
     * @param callable(Exception, Request): (bool)|null $handleRetry
     * @param bool|true $throw
     * @param MockClient|null $mockClient
     * @param false|bool $useExponentialBackoff
     *
     * @return Response
     *
     * @throws FatalRequestException
     * @throws PendingRequestException
     * @throws RequestException
     *
     * @deprecated This method will be removed in Saloon v4. Please refer to the documentation to see connector or request-based retry functionality.
     */
    public function sendAndRetry(
        Request $request,
        $tries,
        $interval = 0,
        callable $handleRetry = null,
        $throw = true,
        MockClient $mockClient = null,
        $useExponentialBackoff = false
    ) {
        $request->tries = $tries;
        $request->retryInterval = $interval;
        $request->throwOnMaxTries = $throw;
        $request->useExponentialBackoff = $useExponentialBackoff;

        return $this->send($request, $mockClient, $handleRetry);
    }

    /**
     * Create a new PendingRequest
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     *
     * @return PendingRequest
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null)
    {
        return new PendingRequest($this, $request, $mockClient);
    }

    /**
     * Create a request pool
     *
     * @param iterable<PromiseInterface|Request>|callable(Connector): iterable<PromiseInterface|Request> $requests
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @param callable(Response, array-key $key, PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     *
     * @return Pool
     */
    public function pool(
        $requests = [],
        $concurrency = 5,
        callable $responseHandler = null,
        callable $exceptionHandler = null
    ) {
        return new Pool($this, $requests, $concurrency, $responseHandler, $exceptionHandler);
    }
}
