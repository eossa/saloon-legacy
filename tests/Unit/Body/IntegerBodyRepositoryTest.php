<?php

namespace Saloon\Tests\Unit\Body;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\IntegerStore;

class IntegerBodyRepositoryTest extends TestCase
{
    public function testTheStoreIsEmptyByDefault()
    {
        $store = new IntegerStore();

        $this->assertEquals(null, $store->get());
    }

    public function testTheStoreCanHaveAnIntegerProvided()
    {
        $store = new IntegerStore(1);

        $this->assertEquals(1, $store->get());
    }

    public function testYouCanSetIt()
    {
        $store = new IntegerStore();

        $store->set(1);

        $this->assertEquals(1, $store->get());
    }

    public function testYouCanCheckIfTheStoreIsEmpty()
    {
        $store = new IntegerStore();

        $this->assertTrue($store->isEmpty());
        $this->assertFalse($store->isNotEmpty());

        $store->set(0);

        $this->assertTrue($store->isEmpty());
        $this->assertFalse($store->isNotEmpty());

        $store->set(1);

        $this->assertFalse($store->isEmpty());
        $this->assertTrue($store->isNotEmpty());
    }
}
