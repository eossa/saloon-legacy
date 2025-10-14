<?php

namespace Saloon\Tests\Unit;

use Saloon\Repositories\ArrayStore;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\DefaultPropertiesRequest;
use PHPUnit\Framework\TestCase;

class RequestPropertiesTest extends TestCase
{
    public function testYouCanRetrieveAllTheRequestParametersMethods()
    {
        $request = new UserRequest();

        $this->assertInstanceOf(ArrayStore::class, $request->headers());
        $this->assertInstanceOf(ArrayStore::class, $request->query());
        $this->assertInstanceOf(ArrayStore::class, $request->config());
        $this->assertInstanceOf(MiddlewarePipeline::class, $request->middleware());
    }

    public function testAllOfTheRequestPropertiesCanHaveDefaultProperties()
    {
        $request = new DefaultPropertiesRequest();

        $this->assertEquals(new ArrayStore(['X-Favourite-Artist' => 'Luke Combs']), $request->headers());
        $this->assertEquals(new ArrayStore(['format' => 'json']), $request->query());
        $this->assertEquals(new ArrayStore(['debug' => true]), $request->config());
    }
}
