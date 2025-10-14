<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Auth\RequiresAuth;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Authenticators\PizzaAuthenticator;

class DefaultPizzaAuthenticatorRequest extends Request
{
    use RequiresAuth;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected $method = Method::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected $connector = TestConnector::class;

    /**
     * @var int|null
     */
    public $userId;

    /**
     * @var int|null
     */
    public $groupId;

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
     * @return Authenticator|null
     */
    public function defaultAuth()
    {
        return new PizzaAuthenticator('BBQ Chicken', 'Lemonade');
    }

    /**
     * @param int|null $userId
     * @param int|null $groupId
     */
    public function __construct($userId = null, $groupId = null)
    {
        $this->userId = $userId;
        $this->groupId = $groupId;
    }
}
