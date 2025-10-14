<?php

namespace Saloon\Tests\Unit\RequestProperties;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\ArrayStore;
use Saloon\Tests\Fixtures\Requests\ConfigRequest;
use Saloon\Tests\Fixtures\Connectors\ConfigConnector;

class ConfigTest extends TestCase
{
    public function testDefaultConfigIsMergedInFromARequest()
    {
        $request = new ConfigRequest();

        $config = $request->config();

        $this->assertInstanceOf(ArrayStore::class, $config);
        $this->assertEquals(new ArrayStore(['debug' => false]), $config);
    }

    public function testConfigCanBeManagedOnARequest()
    {
        $request = new ConfigRequest();

        $config = $request->config()->add('timeout', 60);

        $this->assertInstanceOf(ArrayStore::class, $config);

        $config = $request->config()->merge(['name' => 'Sam', 'category' => 'Cowboy'], ['connect_timeout' => 200]);

        $this->assertInstanceOf(ArrayStore::class, $config);

        $config = $request->config()->remove('category');

        $this->assertInstanceOf(ArrayStore::class, $config);

        $this->assertEquals([
            'timeout' => 60,
            'name' => 'Sam',
            'connect_timeout' => 200,
            'debug' => false,
        ], $config->all());

        $this->assertEquals(60, $config->get('timeout'));

        $config = $request->config()->set(['debug' => true]);

        $this->assertInstanceOf(ArrayStore::class, $config);

        $this->assertEquals(['debug' => true], $request->config()->all());

        $this->assertFalse($request->config()->isEmpty());
        $this->assertTrue($request->config()->isNotEmpty());
    }

    public function testConfigCanBeManagedOnAConnector()
    {
        $connector = new ConfigConnector();

        $config = $connector->config()->add('timeout', 60);

        $this->assertInstanceOf(ArrayStore::class, $config);

        $config = $connector->config()->merge(['name' => 'Sam', 'category' => 'Cowboy'], ['connect_timeout' => 200]);

        $this->assertInstanceOf(ArrayStore::class, $config);

        $config = $connector->config()->remove('category');

        $this->assertInstanceOf(ArrayStore::class, $config);

        $this->assertEquals([
            'timeout' => 60,
            'name' => 'Sam',
            'connect_timeout' => 200,
            'debug' => false,
        ], $config->all());

        $this->assertEquals(60, $config->get('timeout'));

        $config = $connector->config()->set(['debug' => true]);

        $this->assertInstanceOf(ArrayStore::class, $config);

        $this->assertEquals(['debug' => true], $connector->config()->all());

        $this->assertFalse($connector->config()->isEmpty());
        $this->assertTrue($connector->config()->isNotEmpty());
    }
}
