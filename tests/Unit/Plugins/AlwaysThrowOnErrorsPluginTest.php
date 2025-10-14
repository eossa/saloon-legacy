<?php

namespace Saloon\Tests\Unit\Plugins;

use PHPUnit\Framework\TestCase;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;

class AlwaysThrowOnErrorsPluginTest extends TestCase
{
    public function testItAlwaysThrowsAnErrorIfThePluginHasBeenAdded()
    {
        $this->expectException(RequestException::class);

        connector()->send(new AlwaysThrowRequest);
    }
}
