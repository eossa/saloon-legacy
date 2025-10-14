<?php

namespace Saloon\Tests\Fixtures\Authenticators;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;

class PizzaAuthenticator implements Authenticator
{
    /**
     * @var string
     */
    private $pizza;
    /**
     * @var string
     */
    private $drink;

    /**
     * @param string $pizza
     * @param string $drink
     */
    public function __construct(
        $pizza,
        $drink
    ) {
        $this->pizza = $pizza;
        $this->drink = $drink;
    }

    /**
     * Set the pending request.
     *
     * @return void
     */
    public function set(PendingRequest $pendingRequest)
    {
        $pendingRequest->headers()->add('X-Pizza', $this->pizza);
        $pendingRequest->headers()->add('X-Drink', $this->drink);

        $pendingRequest->config()->add('debug', true);
    }
}
