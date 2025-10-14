<?php

namespace Saloon\Tests\Unit\Body;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\Body\StringBodyRepository;

class StringBodyRepositoryTest extends TestCase
{
    public function testTheStoreIsEmptyByDefault()
    {
        $body = new StringBodyRepository();

        $this->assertNull($body->all());
    }

    public function testTheStoreCanHaveADefaultStringProvided()
    {
        $body = new StringBodyRepository('Yeehaw!');

        $this->assertEquals('Yeehaw!', $body->all());
    }

    public function testYouCanSetIt()
    {
        $body = new StringBodyRepository('Sam');

        $body->set('Yeehaw!');

        $this->assertEquals('Yeehaw!', $body->all());
    }

    public function testYouCanConditionallySetOnTheStore()
    {
        $body = new StringBodyRepository();

        $body->when(true, function (StringBodyRepository $body) {
            $body->set('Gareth');
        });
        $body->when(false, function (StringBodyRepository $body) {
            $body->set('Sam');
        });

        $this->assertEquals('Gareth', $body->all());
    }

    public function testYouCanCheckIfTheStoreIsEmptyOrNot()
    {
        $body = new StringBodyRepository();

        $this->assertTrue($body->isEmpty());
        $this->assertFalse($body->isNotEmpty());

        $body->set('Sam');

        $this->assertFalse($body->isEmpty());
        $this->assertTrue($body->isNotEmpty());
    }
}
