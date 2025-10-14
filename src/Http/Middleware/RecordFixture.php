<?php

namespace Saloon\Http\Middleware;

use Saloon\Exceptions\FixtureException;
use Saloon\Http\Response;
use Saloon\Http\Faking\Fixture;
use Saloon\Data\RecordedResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\ResponseMiddleware;

class RecordFixture implements ResponseMiddleware
{
    /**
     * The Fixture
     *
     * @var Fixture
     */
    protected $fixture;

    /**
     * Mock Client
     *
     * @var MockClient
     */
    protected $mockClient;

    /**
     * Constructor
     */
    public function __construct(Fixture $fixture, MockClient $mockClient)
    {
        $this->fixture = $fixture;
        $this->mockClient = $mockClient;
    }

    /**
     * Store the response
     *
     * @return void
     * @throws FixtureException
     */
    public function __invoke(Response $response)
    {
        $this->fixture->store(
            RecordedResponse::fromResponse($response)
        );

        $this->mockClient->recordResponse($response);
    }
}
