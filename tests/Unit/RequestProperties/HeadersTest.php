<?php

namespace Saloon\Tests\Unit\RequestProperties;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\ArrayStore;
use Saloon\Tests\Fixtures\Requests\HeaderRequest;
use Saloon\Tests\Fixtures\Connectors\HeaderConnector;

class HeadersTest extends TestCase
{
    public function testDefaultHeadersAreMergedInFromARequest()
    {
        $request = new HeaderRequest();

        $headers = $request->headers();

        $this->assertInstanceOf(ArrayStore::class, $headers);
        $this->assertEquals(new ArrayStore(['X-Custom-Header' => 'Howdy']), $headers);
    }

    public function testHeadersCanBeManagedOnARequest()
    {
        $request = new HeaderRequest();

        $headers = $request->headers()->add('Content-Type', 'custom/saloon');

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $headers = $request->headers()->merge(['X-Merge-A' => 'Hello', 'Complex' => ['A', 'B']], ['X-Merge-B' => 'Goodbye', 'Content-Type' => 'overwritten']);

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $headers = $request->headers()->remove('X-Merge-B');

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $this->assertEquals([
            'X-Custom-Header' => 'Howdy',
            'Content-Type' => 'overwritten',
            'X-Merge-A' => 'Hello',
            'Complex' => ['A', 'B'],
        ], $headers->all());

        $this->assertEquals('Howdy', $headers->get('X-Custom-Header'));
        $this->assertEquals(['A', 'B'], $headers->get('Complex'));

        $headers = $request->headers()->set(['X-Different' => 'Yo']);

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $this->assertEquals(['X-Different' => 'Yo'], $request->headers()->all());

        $this->assertFalse($request->headers()->isEmpty());
        $this->assertTrue($request->headers()->isNotEmpty());
    }

    public function testHeadersCanBeManagedOnAConnector()
    {
        $connector = new HeaderConnector();

        $headers = $connector->headers()->add('Content-Type', 'custom/saloon');

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $headers = $connector->headers()->merge(['X-Merge-A' => 'Hello', 'Complex' => ['A', 'B']], ['X-Merge-B' => 'Goodbye', 'Content-Type' => 'overwritten']);

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $headers = $connector->headers()->remove('X-Merge-B');

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $this->assertEquals([
            'X-Connector-Header' => 'Sam',
            'Content-Type' => 'overwritten',
            'X-Merge-A' => 'Hello',
            'Complex' => ['A', 'B'],
        ], $headers->all());

        $this->assertEquals('Sam', $headers->get('X-Connector-Header'));
        $this->assertEquals(['A', 'B'], $headers->get('Complex'));

        $headers = $connector->headers()->set(['X-Different' => 'Yo']);

        $this->assertInstanceOf(ArrayStore::class, $headers);

        $this->assertEquals(['X-Different' => 'Yo'], $connector->headers()->all());

        $this->assertFalse($connector->headers()->isEmpty());
        $this->assertTrue($connector->headers()->isNotEmpty());
    }
}
