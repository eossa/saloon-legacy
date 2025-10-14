<?php

namespace Saloon\Traits;

use Saloon\Http\Faking\MockClient;

trait HasMockClient
{
    /**
     * Mock Client
     *
     * @var ?MockClient
     */
    protected $mockClient = null;

    /**
     * Specify a mock client.
     *
     * @return $this
     */
    public function withMockClient(MockClient $mockClient)
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Get the mock client.
     *
     * @return MockClient|null
     */
    public function getMockClient()
    {
        return $this->mockClient;
    }

    /**
     * Determine if the instance has a mock client
     *
     * @return bool
     */
    public function hasMockClient()
    {
        return $this->mockClient instanceof MockClient;
    }
}
