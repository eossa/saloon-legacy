<?php

namespace Saloon\Tests\Unit\RequestProperties;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\ArrayStore;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;

class QueryTest extends TestCase
{
    public function testDefaultQueryParametersAreMergedInFromARequest()
    {
        $request = new QueryParameterRequest();

        $query = $request->query();

        $this->assertInstanceOf(ArrayStore::class, $query);
        $this->assertEquals(new ArrayStore(['per_page' => 100]), $query);
    }

    public function testQueryParametersCanBeManagedOnARequest()
    {
        $request = new QueryParameterRequest();

        $query = $request->query()->add('page', 1);

        $this->assertInstanceOf(ArrayStore::class, $query);

        $query = $request->query()->merge(['search' => 'Sam', 'category' => 'Cowboy'], ['per_page' => 200]);

        $this->assertInstanceOf(ArrayStore::class, $query);

        $query = $request->query()->remove('category');

        $this->assertInstanceOf(ArrayStore::class, $query);

        $this->assertEquals([
            'per_page' => 200,
            'page' => 1,
            'search' => 'Sam',
        ], $query->all());

        $this->assertEquals(1, $query->get('page'));

        $query = $request->query()->set(['debug' => true]);

        $this->assertInstanceOf(ArrayStore::class, $query);

        $this->assertEquals(['debug' => true], $request->query()->all());

        $this->assertFalse($request->query()->isEmpty());
        $this->assertTrue($request->query()->isNotEmpty());
    }

    public function testQueryParametersCanBeManagedOnAConnector()
    {
        $connector = new QueryParameterConnector();

        $query = $connector->query()->add('page', 1);

        $this->assertInstanceOf(ArrayStore::class, $query);

        $query = $connector->query()->merge(['search' => 'Sam', 'category' => 'Cowboy'], ['sort' => 'last_name']);

        $this->assertInstanceOf(ArrayStore::class, $query);

        $query = $connector->query()->remove('category');

        $this->assertInstanceOf(ArrayStore::class, $query);

        $this->assertEquals([
            'sort' => 'last_name',
            'page' => 1,
            'search' => 'Sam',
        ], $query->all());

        $this->assertEquals(1, $query->get('page'));

        $query = $connector->query()->set(['debug' => true]);

        $this->assertInstanceOf(ArrayStore::class, $query);

        $this->assertEquals(['debug' => true], $connector->query()->all());

        $this->assertFalse($connector->query()->isEmpty());
        $this->assertTrue($connector->query()->isNotEmpty());
    }
}
