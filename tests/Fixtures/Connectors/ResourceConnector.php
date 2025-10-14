<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Tests\Fixtures\Resources\UserBaseResource;

class ResourceConnector extends TestConnector
{
    /**
     * @return UserBaseResource
     */
    public function user()
    {
        return new UserBaseResource($this);
    }
}
