<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Auth\RequiresAuth;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultAuthenticatorRequest extends Request
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
     * Provide default authentication.
     *
     * @return Authenticator|null
     */
    protected function defaultAuth()
    {
        return new TokenAuthenticator('yee-haw-request');
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
