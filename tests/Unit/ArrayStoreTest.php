<?php

namespace Saloon\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\ArrayStore;

class ArrayStoreTest extends TestCase
{
    public function testTheStoreIsEmptyByDefault()
    {
        $store = new ArrayStore();

        $this->assertEquals([], $store->all());
    }

    public function testYouCanSetIt()
    {
        $store = new ArrayStore();

        $store->set(['name' => 'Sam']);

        $this->assertEquals(['name' => 'Sam'], $store->all());
    }

    public function testYouCanAddAnItem()
    {
        $store = new ArrayStore();
        $store->add('name', 'Sam');

        $this->assertEquals(['name' => 'Sam'], $store->all());
    }

    public function testYouCanConditionallyAddItemsToTheArrayStore()
    {
        $store = new ArrayStore();

        $store->when(true, function (ArrayStore $store) {
            $store->add('name', 'Gareth');
        });
        $store->when(false, function (ArrayStore $store) {
            $store->add('name', 'Sam');
        });
        $store->when(true, function (ArrayStore $store) {
            $store->add('sidekick', 'Mantas');
        });
        $store->when(false, function (ArrayStore $store) {
            $store->add('sidekick', 'Teo');
        });

        $this->assertEquals(['name' => 'Gareth', 'sidekick' => 'Mantas'], $store->all());
    }

    public function testYouCanDeleteAnItem()
    {
        $store = new ArrayStore(['name' => 'Sam']);
        $store->remove('name');

        $this->assertEquals([], $store->all());
    }

    public function testYouCanGetAnItem()
    {
        $store = new ArrayStore(['name' => 'Sam']);

        $this->assertEquals('Sam', $store->get('name'));
    }

    public function testYouCanGetAllItems()
    {
        $store = new ArrayStore(['name' => 'Sam', 'superhero' => 'Iron Man']);

        $this->assertEquals(['name' => 'Sam', 'superhero' => 'Iron Man'], $store->all());
    }

    public function testYouCanMergeItemsTogetherIntoTheContentStore()
    {
        $store = new ArrayStore(['name' => 'Sam', 'superhero' => 'Iron Man']);

        $store->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

        $this->assertEquals(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow'], $store->all());
    }

    public function testYouCanCheckIfTheStoreIsEmptyOrNot()
    {
        $store = new ArrayStore();

        $this->assertTrue($store->isEmpty());
        $this->assertFalse($store->isNotEmpty());

        $store->add('name', 'Sam');

        $this->assertFalse($store->isEmpty());
        $this->assertTrue($store->isNotEmpty());
    }
}
