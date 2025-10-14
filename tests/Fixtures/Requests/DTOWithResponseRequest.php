<?php

namespace Saloon\Tests\Fixtures\Requests;

use Exception;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Data\UserWithResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DTOWithResponseRequest extends Request
{
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
        $this->userId = $userId;
        $this->groupId = $groupId;
    }

    /**
     * Cast to a User.
     *
     * @return object
     *
     * @throws Exception
     */
    public function createDtoFromResponse(Response $response)
    {
        return UserWithResponse::fromResponse($response);
    }
}
