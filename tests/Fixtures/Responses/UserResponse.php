<?php

namespace Saloon\Tests\Fixtures\Responses;

use Exception;
use Saloon\Http\Response;

class UserResponse extends Response
{
    /**
     * @return UserData
     *
     * @throws Exception
     */
    public function customCastMethod()
    {
        return new UserData($this->json('foo'));
    }

    /**
     * @return string|null
     *
     * @throws Exception
     */
    public function foo()
    {
        return $this->json('foo');
    }
}
