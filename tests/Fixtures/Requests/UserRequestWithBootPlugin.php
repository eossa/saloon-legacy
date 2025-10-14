<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Plugins\WithBootTestPlugin;

class UserRequestWithBootPlugin extends Request
{
    use WithBootTestPlugin;

    public $userId = null;
    public $groupId = null;

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
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }

    /**
     * @param int|null $userId
     * @param int|null $groupId
     */
    public function __construct($userId = null, $groupId = null)
    {
        $this->groupId = $groupId;
        $this->userId = $userId;
    }
}
