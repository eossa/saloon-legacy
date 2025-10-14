<?php

namespace Saloon\Tests\Unit;

use Generator;
use Traversable;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use PHPUnit\Framework\TestCase;

class PoolTest extends TestCase
{
    public function testAcceptsAnArrayForRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
        ];

        $pool = $connector->pool($requests);

        $pool->setConcurrency(5);

        $pool->withResponseHandler(function (Response $response, $index) use ($requests, &$count) {
            $this->assertSame($requests[$index], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
    }

    public function testAcceptsAnArrayForAliasedRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [
            'a' => new UserRequest(),
            'b' => new UserRequest(),
            'c' => new UserRequest(),
        ];

        $pool = $connector->pool($requests);

        $pool->setConcurrency(5);

        $pool->withResponseHandler(function (Response $response, $name) use ($requests, &$count) {
            $this->assertSame($requests[$name], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
        $this->assertArrayHasKey('a', $requests);
        $this->assertArrayHasKey('b', $requests);
        $this->assertArrayHasKey('c', $requests);
    }

    public function testAcceptsAGeneratorForRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [];

        $generatorCallback = function () use (&$requests) {
            for ($i = 0; $i < 3; $i++) {
                $request = new UserRequest();
                $requests[$i] = $request;

                yield $i => $request;
            }
        };

        $this->assertTrue(is_callable($generatorCallback));
        $this->assertInstanceOf(Generator::class, $generatorCallback());

        $pool = $connector->pool($generatorCallback());
        $pool->setConcurrency(5);
        $pool->withResponseHandler(function (Response $response, $index) use (&$requests, &$count) {
            $this->assertSame($requests[$index], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
    }

    public function testAcceptsAGeneratorForAliasedRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [];

        $generatorCallback = function () use (&$requests) {
            for ($name = 'a'; $name !== 'd'; $name++) {
                $request = new UserRequest();
                $requests[$name] = $request;

                yield $name => $request;
            }
        };

        $this->assertTrue(is_callable($generatorCallback));
        $this->assertInstanceOf(Generator::class, $generatorCallback());

        $pool = $connector->pool($generatorCallback());
        $pool->setConcurrency(5);
        $pool->withResponseHandler(function (Response $response, $name) use (&$requests, &$count) {
            $this->assertSame($requests[$name], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
        $this->assertArrayHasKey('a', $requests);
        $this->assertArrayHasKey('b', $requests);
        $this->assertArrayHasKey('c', $requests);
    }

    public function testAcceptsACallbackThatReturnsAnArrayForRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [];

        $arrayCallback = function () use (&$requests) {
            $requests = array_merge($requests, [
                new UserRequest(),
                new UserRequest(),
                new UserRequest(),
            ]);

            return $requests;
        };

        $this->assertTrue(is_callable($arrayCallback));
        $this->assertTrue(is_array($requests));

        $pool = $connector->pool($arrayCallback);

        $pool->setConcurrency(5);

        $pool->withResponseHandler(function (Response $response, $index) use (&$requests, &$count) {
            $this->assertSame($requests[$index], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
    }

    public function testAcceptsACallbackThatReturnsAnArrayForAliasedRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [];

        $arrayCallback = function (Connector $callbackConnector) use (&$requests, $connector) {
            $this->assertEquals($connector, $callbackConnector);

            $requests = array_merge($requests, [
                'a' => new UserRequest(),
                'b' => new UserRequest(),
                'c' => new UserRequest(),
            ]);

            return $requests;
        };

        $this->assertTrue(is_callable($arrayCallback));
        $this->assertTrue(is_array($requests));

        $pool = $connector->pool($arrayCallback);
        $pool->setConcurrency(5);
        $pool->withResponseHandler(function (Response $response, $name) use (&$requests, &$count) {
            $this->assertSame($requests[$name], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
        $this->assertArrayHasKey('a', $requests);
        $this->assertArrayHasKey('b', $requests);
        $this->assertArrayHasKey('c', $requests);
    }

    public function testAcceptsACallbackThatReturnsAGeneratorForRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [];

        $generatorCallback = function () use (&$requests) {
            for ($i = 0; $i < 3; $i++) {
                $request = new UserRequest();
                $requests[$i] = $request;

                yield $i => $request;
            }
        };

        $this->assertTrue(is_callable($generatorCallback));
        $this->assertInstanceOf(Generator::class, $generatorCallback());

        $pool = $connector->pool($generatorCallback);
        $pool->setConcurrency(5);
        $pool->withResponseHandler(function (Response $response, $index) use (&$requests, &$count) {
            $this->assertSame($requests[$index], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
    }

    public function testAcceptsACallbackThatReturnsAGeneratorForAliasedRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $count = 0;

        $requests = [];

        $generatorCallback = function () use (&$requests) {
            for ($name = 'a'; $name !== 'd'; $name++) {
                $request = new UserRequest();
                $requests[$name] = $request;

                yield $name => $request;
            }
        };

        $this->assertTrue(is_callable($generatorCallback));
        $this->assertInstanceOf(Generator::class, $generatorCallback());

        $pool = $connector->pool($generatorCallback);
        $pool->setConcurrency(5);
        $pool->withResponseHandler(function (Response $response, $name) use (&$requests, &$count) {
            $this->assertSame($requests[$name], $response->getRequest());

            $count++;
        });

        $pool->send()->wait();

        $this->assertEquals(3, $count);
        $this->assertArrayHasKey('a', $requests);
        $this->assertArrayHasKey('b', $requests);
        $this->assertArrayHasKey('c', $requests);
    }

    public function testThrowsAnExceptionIfAnInvalidItemIsPassedIntoTheIterator()
    {
        $connector = new TestConnector();

        $pool = $connector->pool([
            new UserRequest(),
            new UserRequest(),
            new TestConnector(),
        ]);

        $this->expectException(InvalidPoolItemException::class);
        $pool->send()->wait();
    }

    public function testYouCanGetTheRequestsProvidedIntoThePool()
    {
        $connector = new TestConnector();

        $requests = [
            new UserRequest(),
            new UserRequest(),
            new TestConnector(),
        ];

        $pool = $connector->pool($requests);
        $iterable = $pool->getRequests();

        $this->assertTrue(is_array($iterable) || $iterable instanceof Traversable);

        $index = null;
        foreach ($iterable as $index => $request) {
            $this->assertEquals($requests[$index], $request);
        }

        $this->assertEquals(2, $index);
    }
}
