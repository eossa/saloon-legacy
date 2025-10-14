<?php

namespace Saloon\Tests\Unit\Body;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Repositories\Body\ArrayBodyRepository;

class ArrayBodyRepositoryTest extends TestCase
{
    public function testTheStoreIsEmptyByDefault()
    {
        $body = new ArrayBodyRepository();

        $this->assertEquals([], $body->all());
    }

    public function testTheStoreCanHaveAnArrayProvided()
    {
        $body = new ArrayBodyRepository([
            'name' => 'Sam',
            'sidekick' => 'Mantas',
        ]);

        $this->assertEquals([
            'name' => 'Sam',
            'sidekick' => 'Mantas',
        ], $body->all());
    }

    public function testYouCanSetIt()
    {
        $body = new ArrayBodyRepository();

        $body->set(['name' => 'Sam']);

        $this->assertEquals(['name' => 'Sam'], $body->all());
    }

    public function testItWillThrowAnExceptionIfYouSetANonArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value must be an array');

        $body = new ArrayBodyRepository();
        $body->set('Sam');
    }

    public function testYouCanAddAnItem()
    {
        $body = new ArrayBodyRepository();

        $body->add('name', 'Sam');

        $this->assertEquals(['name' => 'Sam'], $body->all());
    }

    public function testYouCanAddAnItemWithAnIntegerKey()
    {
        $body = new ArrayBodyRepository();

        $body->add(1, 'Sam');

        $this->assertEquals([1 => 'Sam'], $body->all());
    }

    public function testYouCanAddAnItemWithoutAKey()
    {
        $body = new ArrayBodyRepository();

        $body->add(null, 'Sam');

        $this->assertEquals(['Sam'], $body->all());
    }

    public function testYouCanConditionallyAddItemsToTheArrayStore()
    {
        $body = new ArrayBodyRepository();

        $body->when(true, function (ArrayBodyRepository $body) {
            $body->add('name', 'Gareth');
        });
        $body->when(false, function (ArrayBodyRepository $body) {
            $body->add('name', 'Sam');
        });
        $body->when(true, function (ArrayBodyRepository $body) {
            $body->add('sidekick', 'Mantas');
        });
        $body->when(false, function (ArrayBodyRepository $body) {
            $body->add('sidekick', 'Teo');
        });

        $this->assertEquals(['name' => 'Gareth', 'sidekick' => 'Mantas'], $body->all());
    }

    public function testYouCanDeleteAnItem()
    {
        $body = new ArrayBodyRepository();

        $body->add('name', 'Sam');
        $body->remove('name');

        $this->assertEquals([], $body->all());
    }

    public function testYouCanDeleteAnItemWithAnIntegerKey()
    {
        $body = new ArrayBodyRepository();

        $body->add(1, 'Sam');
        $body->remove(1);

        $this->assertEquals([], $body->all());
    }

    public function testYouCanGetAnItem()
    {
        $body = new ArrayBodyRepository();

        $body->add('name', 'Sam');

        $this->assertEquals('Sam', $body->get('name'));

        // When omitting the key it should act like `->all()`
        $this->assertEquals(['name' => 'Sam'], $body->all());
    }

    public function testYouCanGetAnItemWithAnIntegerKey()
    {
        $body = new ArrayBodyRepository();

        $body->add(2, 'Sam');

        $this->assertEquals('Sam', $body->get(2));
    }

    public function testYouCanGetAllItems()
    {
        $body = new ArrayBodyRepository();

        $body->add('name', 'Sam');
        $body->add('superhero', 'Iron Man');

        $allResults = ['name' => 'Sam', 'superhero' => 'Iron Man'];

        $this->assertEquals($allResults, $body->all());
        $this->assertEquals($allResults, $body->all());
    }

    public function testYouCanMergeItemsTogetherIntoTheBodyRepository()
    {
        $body = new ArrayBodyRepository();

        $this->assertInstanceOf(MergeableBody::class, $body);

        $body->add('name', 'Sam');
        $body->add('sidekick', 'Mantas');

        $body->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

        $this->assertEquals(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow'], $body->all());
    }

    public function testYouCanCheckIfTheStoreIsEmptyOrNot()
    {
        $body = new ArrayBodyRepository();

        $this->assertTrue($body->isEmpty());
        $this->assertFalse($body->isNotEmpty());

        $body->add('name', 'Sam');

        $this->assertFalse($body->isEmpty());
        $this->assertTrue($body->isNotEmpty());
    }
}
