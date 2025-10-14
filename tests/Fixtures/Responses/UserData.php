<?php

namespace Saloon\Tests\Fixtures\Responses;

class UserData
{
    /**
     * @var string
     */
    public $foo;

    /**
     * CustomResponse constructor.
     *
     * @param string $foo
     */
    public function __construct(
        $foo
    ) {
        $this->foo = $foo;
    }
}
