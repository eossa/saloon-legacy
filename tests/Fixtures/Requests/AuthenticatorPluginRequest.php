<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Plugins\AuthenticatorPlugin;

class AuthenticatorPluginRequest extends Request
{
    use AuthenticatorPlugin;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * The connector.
     *
     * @var string
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
     * @param int|null $userId
     * @param int|null $groupId
     */
    public function __construct($userId = null, $groupId = null)
    {
        $this->userId = $userId;
        $this->groupId = $groupId;
    }


    /**
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }
}
