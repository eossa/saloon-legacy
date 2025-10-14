<?php

namespace Saloon\Tests\Unit\Body;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Saloon\Repositories\Body\StreamBodyRepository;

class StreamBodyRepositoryTest extends TestCase
{
    public function testTheStoreIsEmptyByDefault()
    {
        $body = new StreamBodyRepository();

        $this->assertNull($body->all());
        $this->assertNull($body->get());
    }

    public function testTheStoreCanHaveADefaultStreamProvided()
    {
        $resource = tmpfile();

        $body = new StreamBodyRepository($resource);

        $this->assertEquals($resource, $body->all());
        $this->assertEquals($resource, $body->get());
    }

    public function testYouCanSetIt()
    {
        $resourceA = fopen('php://memory', 'rw+');
        fwrite($resourceA, 'Howdy');

        $resourceB = fopen('php://memory', 'rw+');
        fwrite($resourceB, 'Yeehaw');

        $body = new StreamBodyRepository($resourceA);

        $body->set($resourceB);

        $this->assertEquals($resourceB, $body->get());
    }

    public function testYouCanSetAnInstanceOfStreamInterface()
    {
        $streamA = Utils::streamFor('Howdy!');
        $streamB = Utils::streamFor('Partner!');

        $body = new StreamBodyRepository($streamA);
        $body->set($streamB);

        $this->assertSame($streamB, $body->get());
    }

    public function testYouCanConditionallySetOnTheStore()
    {
        $body = new StreamBodyRepository();

        $resourceA = fopen('php://memory', 'rw+');
        fwrite($resourceA, 'Howdy');

        $resourceB = fopen('php://memory', 'rw+');
        fwrite($resourceB, 'Yeehaw');

        $body->when(true, function (StreamBodyRepository $body) use ($resourceA) {
            $body->set($resourceA);
        });
        $body->when(false, function (StreamBodyRepository $body) use ($resourceB) {
            $body->set($resourceB);
        });

        $this->assertEquals($resourceA, $body->get());
    }

    public function testYouCanCheckIfTheStoreIsEmptyOrNot()
    {
        $body = new StreamBodyRepository();

        $this->assertTrue($body->isEmpty());
        $this->assertFalse($body->isNotEmpty());

        $body->set(tmpfile());

        $this->assertFalse($body->isEmpty());
        $this->assertTrue($body->isNotEmpty());
    }

    public function testItWillThrowAnExceptionIfTheValueIsNotAResourceOrStreamInterfaceWhenInstantiating()
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamBodyRepository('Howdy');
    }

    public function testItWillThrowAnExceptionForInteger()
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamBodyRepository(123);
    }

    public function testItWillThrowAnExceptionForArray()
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamBodyRepository([]);
    }

    public function testItWillThrowAnExceptionForBoolean()
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamBodyRepository(false);
    }

    public function testItWillThrowAnExceptionIfTheValueIsNotAResourceOrStreamInterfaceWhenSetting()
    {
        $body = new StreamBodyRepository();

        $this->expectException(InvalidArgumentException::class);
        $body->set('Howdy');
    }

    public function testItWillThrowAnExceptionWhenSettingInteger()
    {
        $body = new StreamBodyRepository();

        $this->expectException(InvalidArgumentException::class);
        $body->set(123);
    }

    public function testItWillThrowAnExceptionWhenSettingArray()
    {
        $body = new StreamBodyRepository();

        $this->expectException(InvalidArgumentException::class);
        $body->set([]);
    }

    public function testItWillThrowAnExceptionWhenSettingBoolean()
    {
        $body = new StreamBodyRepository();

        $this->expectException(InvalidArgumentException::class);
        $body->set(false);
    }

    public function testItAllowsNullValues()
    {
        $body = new StreamBodyRepository(null);

        $this->assertNull($body->get());
        $this->assertTrue($body->isEmpty());

        $body->set(null);

        $this->assertNull($body->get());
        $this->assertTrue($body->isEmpty());
    }
}
