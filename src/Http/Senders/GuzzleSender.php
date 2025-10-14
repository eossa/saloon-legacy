<?php

namespace Saloon\Http\Senders;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Saloon\Config;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Http\Response;
use GuzzleHttp\HandlerStack;
use Saloon\Contracts\Sender;
use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Data\FactoryCollection;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Senders\Factories\GuzzleMultipartBodyFactory;

class GuzzleSender implements Sender
{
    /**
     * The Guzzle client.
     *
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Guzzle's Handler Stack.
     *
     * @var HandlerStack
     */
    protected $handlerStack;

    /**
     * Constructor
     *
     * Create the HTTP client.
     */
    public function __construct()
    {
        $this->client = $this->createGuzzleClient();
    }

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
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     */
    protected function createGuzzleClient()
    {
        // We'll use HandlerStack::create as it will create a default
        // handler stack with the default Guzzle middleware like
        // http_errors, cookies etc.

        $this->handlerStack = HandlerStack::create();

        // Now we'll return new Guzzle client with some default request
        // options configured. We'll also define the handler stack we
        // created above. Since it's a property, developers may
        // customise or add middleware to the handler stack.

        return new GuzzleClient([
            'crypto_method' => Config::$defaultTlsMethod,
            RequestOptions::CONNECT_TIMEOUT => Config::$defaultConnectionTimeout,
            RequestOptions::TIMEOUT => Config::$defaultRequestTimeout,
            RequestOptions::HTTP_ERRORS => true,
            'handler' => $this->handlerStack,
        ]);
    }

    /**
     * Send a synchronous request.
     *
     * @return Response
     *
     * @throws GuzzleException
     * @throws FatalRequestException
     * @throws InvalidResponseClassException
     */
    public function send(PendingRequest $pendingRequest)
    {
        $request = $pendingRequest->createPsrRequest();
        $requestOptions = $pendingRequest->config()->all();

        try {
            $guzzleResponse = $this->client->send($request, $requestOptions);

            return $this->createResponse($guzzleResponse, $pendingRequest, $request);
        } catch (ConnectException $exception) {
            // ConnectException means a network exception has happened, like Guzzle
            // not being able to connect to the host.

            throw new FatalRequestException($exception, $pendingRequest);
        } catch (RequestException $exception) {
            // Sometimes, Guzzle will throw a RequestException without a response. This
            // means that it was fatal, so we should still throw a fatal request exception.

            $guzzleResponse = $exception->getResponse();

            if (is_null($guzzleResponse)) {
                throw new FatalRequestException($exception, $pendingRequest);
            }

            return $this->createResponse($guzzleResponse, $pendingRequest, $request, $exception);
        }
    }

    /**
     * Send an asynchronous request
     *
     * @return PromiseInterface
     */
    public function sendAsync(PendingRequest $pendingRequest)
    {
        $request = $pendingRequest->createPsrRequest();
        $requestOptions = $pendingRequest->config()->all();

        $promise = $this->client->sendAsync($request, $requestOptions);

        return $this->processPromise($request, $promise, $pendingRequest);
    }

    /**
     * Update the promise provided by Guzzle.
     *
     * @return PromiseInterface
     */
    protected function processPromise(RequestInterface $psrRequest, PromiseInterface $promise, PendingRequest $pendingRequest)
    {
        return $promise
            ->then(
                function (ResponseInterface $guzzleResponse) use ($psrRequest, $pendingRequest) {
                    // Instead of the promise returning a Guzzle response, we want to return
                    // a Saloon response.

                    return $this->createResponse($guzzleResponse, $pendingRequest, $psrRequest);
                },
                function (TransferException $guzzleException) use ($pendingRequest, $psrRequest) {
                    // When the exception wasn't a RequestException, we'll throw a fatal
                    // exception as this is likely a ConnectException, but it will
                    // catch any new ones Guzzle release.

                    if (! $guzzleException instanceof RequestException) {
                        throw new FatalRequestException($guzzleException, $pendingRequest);
                    }

                    // Sometimes, Guzzle will throw a RequestException without a response. This
                    // means that it was fatal, so we should still throw a fatal request exception.

                    $guzzleResponse = $guzzleException->getResponse();

                    if (is_null($guzzleResponse)) {
                        throw new FatalRequestException($guzzleException, $pendingRequest);
                    }

                    // Otherwise we'll create a response to convert into an exception.
                    // This will run the exception through the exception handlers
                    // which allows the user to handle their own exceptions.

                    $response = $this->createResponse($guzzleResponse, $pendingRequest, $psrRequest, $guzzleException);

                    // Throw the exception our way

                    if (($exception = $response->toException())) {
                        throw $exception;
                    } else {
                        return $response;
                    }
                }
            );
    }

    /**
     * Create a response.
     *
     * @param ResponseInterface $psrResponse
     * @param PendingRequest $pendingRequest
     * @param RequestInterface $psrRequest
     * @param Exception|null $exception
     *
     * @return Response
     *
     * @throws InvalidResponseClassException
     */
    protected function createResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, Exception $exception = null)
    {
        /** @var class-string<Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        return $responseClass::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest, $exception);
    }

    /**
     * Add a middleware to the handler stack.
     *
     * @param callable $callable
     * @param string $name
     *
     * @return $this
     */
    public function addMiddleware(callable $callable, $name = '')
    {
        $this->handlerStack->push($callable, $name);

        return $this;
    }

    /**
     * Overwrite the entire handler stack.
     *
     * @return $this
     */
    public function setHandlerStack(HandlerStack $handlerStack)
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Get the handler stack.
     *
     * @return HandlerStack
     */
    public function getHandlerStack()
    {
        return $this->handlerStack;
    }

    /**
     * Get the Guzzle client
     *
     * @return GuzzleClient
     */
    public function getGuzzleClient()
    {
        return $this->client;
    }
}
