<?php

namespace Saloon\Tests\Fixtures\Resources;

use Exception;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\BaseResource;
use Saloon\Tests\Fixtures\Requests\UserRequest;

class UserBaseResource extends BaseResource
{
    /**
     * Get User
     *
     * @return array
     *
     * @throws PendingRequestException
     * @throws FatalRequestException
     * @throws RequestException
     * @throws Exception
     */
    public function get()
    {
        return $this->connector->send(new UserRequest)->toArray();
    }
}
