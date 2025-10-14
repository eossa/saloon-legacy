<?php

namespace Saloon\Traits\Request;

use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Contracts\Sender;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use GuzzleHttp\Promise\PromiseInterface;

trait HasConnector
{
    /**
     * The loaded connector used in requests.
     *
     * @var ?Connector
     */
    private $loadedConnector = null;

    /**
     *  Retrieve the loaded connector.
     *
     * @return Connector
     */
    public function connector()
    {
        if (isset($this->loadedConnector)) {
            return $this->loadedConnector;
        }
        return $this->loadedConnector = $this->resolveConnector();
    }

    /**
     * Set the loaded connector at runtime.
     *
     * @return $this
     */
    public function setConnector(Connector $connector)
    {
        $this->loadedConnector = $connector;

        return $this;
    }

    /**
     * Create a new connector instance.
     *
     * @return Connector
     */
    protected function resolveConnector()
    {
        return new $this->connector;
    }

    /**
     * Access the HTTP sender
     *
     * @return Sender
     */
    public function sender()
    {
        return $this->connector()->sender();
    }

    /**
     * Create a pending request
     *
     * @param MockClient|null $mockClient
     *
     * @return PendingRequest
     *
     * @throws DuplicatePipeNameException
     */
    public function createPendingRequest(MockClient $mockClient = null)
    {
        return $this->connector()->createPendingRequest($this, $mockClient);
    }

    /**
     * Send a request synchronously
     *
     * @param MockClient|null $mockClient
     *
     * @return Response
     *
     * @throws PendingRequestException
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function send(MockClient $mockClient = null)
    {
        return $this->connector()->send($this, $mockClient);
    }

    /**
     * Send a request asynchronously
     *
     * @param MockClient|null $mockClient
     *
     * @return PromiseInterface
     */
    public function sendAsync(MockClient $mockClient = null)
    {
        return $this->connector()->sendAsync($this, $mockClient);
    }
}
