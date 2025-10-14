<?php

namespace Saloon\Http;

use Saloon\Config;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Helpers\Helpers;
use Saloon\Traits\Macroable;
use Saloon\Helpers\URLHelper;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Contracts\FakeResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\PendingRequest\MergeBody;
use Saloon\Http\PendingRequest\MergeDelay;
use Saloon\Http\Middleware\DelayMiddleware;
use Saloon\Http\PendingRequest\BootPlugins;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Http\Middleware\ValidateProperties;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Traits\PendingRequest\ManagesPsrRequests;
use Saloon\Http\PendingRequest\MergeRequestProperties;
use Saloon\Http\PendingRequest\BootConnectorAndRequest;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Http\PendingRequest\AuthenticatePendingRequest;

class PendingRequest
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use ManagesPsrRequests;
    use Conditionable;
    use HasMockClient;
    use Macroable;

    /**
     * The connector making the request.
     *
     * @var Connector
     */
    protected $connector;

    /**
     * The request used by the instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The method the request will use.
     *
     * @var string
     */
    protected $method;

    /**
     * The URL the request will be made to.
     *
     * @var string
     */
    protected $url;

    /**
     * The body of the request.
     *
     * @var BodyRepository|null
     */
    protected $body = null;

    /**
     * The simulated response.
     *
     * @var FakeResponse|null
     */
    protected $fakeResponse = null;

    /**
     * Determine if the pending request is asynchronous
     *
     * @var bool
     */
    protected $asynchronous = false;

    /**
     * Build up the request payload.
     *
     * @param Connector $connector
     * @param Request $request
     * @param MockClient|null $mockClient
     */
    public function __construct(Connector $connector, Request $request, MockClient $mockClient = null)
    {
        // Let's start by getting our PSR factory collection. This object contains all the
        // relevant factories for creating PSR-7 requests as well as URIs and streams.

        $this->factoryCollection = $connector->sender()->getFactoryCollection();

        // Now we'll set the base properties

        $this->connector = $connector;
        $this->request = $request;
        $this->method = $request->getMethod();
        $this->url = URLHelper::join($this->connector->resolveBaseUrl(), $this->request->resolveEndpoint());
        $requestAuthenticator = $request->getAuthenticator();
        $connectorAuthenticator = $connector->getAuthenticator();
        $this->authenticator = isset($requestAuthenticator) ? $requestAuthenticator : $connectorAuthenticator;
        $requestMockClient = $request->getMockClient();
        $connectorMockClient = $connector->getMockClient();
        $globalMockClient = MockClient::getGlobal();
        $this->mockClient = isset($mockClient)
            ? $mockClient
            : (
                isset($requestMockClient)
                    ? $requestMockClient
                    : (isset($connectorMockClient) ? $connectorMockClient : $globalMockClient)
            );

        // Now, we'll register our global middleware and our mock response middleware.
        // Registering these middleware first means that the mock client can set
        // the fake response for every subsequent middleware.

        $this->middleware()->merge(Config::globalMiddleware());
        $this->middleware()->onRequest(new DetermineMockResponse, 'determineMockResponse');

        // Next, we'll boot our plugins. These plugins can add headers, config variables and
        // even register their own middleware. We'll use a tap method to simply apply logic
        // to the PendingRequest. After that, we will merge together our request properties
        // like headers, config, middleware, body and delay, and we'll follow it up by
        // invoking our authenticators. We'll do this here because when middleware is
        // executed, the developer will have access to any headers added by the middleware.

        $this
            ->tap(new BootPlugins)
            ->tap(new MergeRequestProperties)
            ->tap(new MergeBody)
            ->tap(new MergeDelay)
            ->tap(new AuthenticatePendingRequest)
            ->tap(new BootConnectorAndRequest);

        // Now, we'll register some default middleware for validating the request properties and
        // running the delay that should have been set by the user.

        $this->middleware()
            ->onRequest(new ValidateProperties, 'validateProperties')
            ->onRequest(new DelayMiddleware, 'delayMiddleware');

        // Finally, we will execute the request middleware pipeline which will
        // process the middleware in the order we added it.

        $this->middleware()->executeRequestPipeline($this);
    }

    /**
     * Authenticate the PendingRequest
     *
     * @return $this
     */
    public function authenticate(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;

        // Since the PendingRequest has already been constructed we will run the set
        // method on the authenticator which runs it straight away.

        $this->authenticator->set($this);

        return $this;
    }

    /**
     * Execute the response pipeline.
     *
     * @return Response
     */
    public function executeResponsePipeline(Response $response)
    {
        return $this->middleware()->executeResponsePipeline($response);
    }

    /**
     * Execute the fatal pipeline.
     *
     * @return void
     */
    public function executeFatalPipeline(FatalRequestException $throwable)
    {
        $this->middleware()->executeFatalPipeline($throwable);
    }

    /**
     * Get the request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the connector.
     *
     * @return Connector
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Get the URL of the request.
     *
     * @returns string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the URL of the PendingRequest
     *
     * Note: This will be combined with the query parameters to create
     * a UriInterface that will be passed to a PSR-7 request.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the HTTP method used for the request
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the method of the PendingRequest
     *
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Retrieve the body on the instance
     *
     * @return BodyRepository|null
     */
    public function body()
    {
        return $this->body;
    }

    /**
     * Set the body repository
     *
     * @return $this
     */
    public function setBody(BodyRepository $body = null)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the fake response
     *
     * @return FakeResponse|null
     */
    public function getFakeResponse()
    {
        return $this->fakeResponse;
    }

    /**
     * Set the fake response
     *
     * @return $this
     */
    public function setFakeResponse(FakeResponse $fakeResponse = null)
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Check if a fake response has been set
     *
     * @return bool
     */
    public function hasFakeResponse()
    {
        return $this->fakeResponse instanceof FakeResponse;
    }

    /**
     * Check if the request is asynchronous
     *
     * @return bool
     */
    public function isAsynchronous()
    {
        return $this->asynchronous;
    }

    /**
     * Set if the request is going to be sent asynchronously
     *
     * @var bool $asynchronous
     *
     * @return $this
     */
    public function setAsynchronous($asynchronous)
    {
        $this->asynchronous = $asynchronous;

        return $this;
    }

    /**
     * Get the response class
     *
     * @return class-string<Response>
     *
     * @throws InvalidResponseClassException
     */
    public function getResponseClass()
    {
        $requestResponseClass = $this->request->resolveResponseClass();
        $connectorResponseClass = $this->connector->resolveResponseClass();
        $response = isset($requestResponseClass)
            ? $requestResponseClass
            : (isset($connectorResponseClass) ? $connectorResponseClass : Response::class);

        if (! class_exists($response) || ! Helpers::isSubclassOf($response, Response::class)) {
            throw new InvalidResponseClassException;
        }

        return $response;
    }

    /**
     * Tap into the pending request
     *
     * @return $this
     */
    protected function tap(callable $callable)
    {
        $callable($this);

        return $this;
    }
}
