<?php

namespace Saloon\Tests\Unit\Body;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Saloon\Data\MultipartValue;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Repositories\Body\MultipartBodyRepository;

class MultipartBodyRepositoryTest extends TestCase
{
    public function testTheStoreIsEmptyByDefault()
    {
        $body = new MultipartBodyRepository();

        $this->assertEquals([], $body->all());
    }

    public function testTheStoreCanHaveAnArrayOfMultipartValuesProvided()
    {
        $body = new MultipartBodyRepository([
            new MultipartValue('name', 'Sam'),
            new MultipartValue('sidekick', 'Mantas'),
        ]);

        $this->assertEquals([
            new MultipartValue('name', 'Sam'),
            new MultipartValue('sidekick', 'Mantas'),
        ], $body->all());
    }

    public function testTheStoreWillThrowAnExceptionIfSetValueIsNotAnArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value must be an array');

        $body = new MultipartBodyRepository();
        $body->set('123');
    }

    public function testTheStoreWillThrowAnExceptionIfTheArrayDoesNotContainMultipartValues()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value array must only contain Saloon\Data\MultipartValue objects');

        new MultipartBodyRepository([
            'name' => 'Sam',
            'sidekick' => new MultipartValue('username', 'Sammyjo20'),
        ]);
    }

    public function testYouCanSetIt()
    {
        $body = new MultipartBodyRepository();

        $body->set([
            new MultipartValue('username', 'Sammyjo20'),
        ]);

        $this->assertEquals([
            new MultipartValue('username', 'Sammyjo20'),
        ], $body->all());
    }

    public function testYouCanAddMultipleItems()
    {
        $body = new MultipartBodyRepository();

        $body->add('name', 'Sam', 'welcome.txt', ['a' => 'b']);

        $this->assertEquals([
            new MultipartValue('name', 'Sam', 'welcome.txt', ['a' => 'b']),
        ], $body->all());

        // Test it gets added to the array
        $body->add('name', 'Charlotte', 'welcome.txt', ['a' => 'b']);

        $this->assertEquals([
            new MultipartValue('name', 'Sam', 'welcome.txt', ['a' => 'b']),
            new MultipartValue('name', 'Charlotte', 'welcome.txt', ['a' => 'b']),
        ], $body->all());
    }

    public function testYouCanConditionallyAddItemsToTheArrayStore()
    {
        $body = new MultipartBodyRepository();

        $body->when(true, function (MultipartBodyRepository $body) {
            $body->add('name', 'Gareth');
        });
        $body->when(false, function (MultipartBodyRepository $body) {
            $body->add('name', 'Sam');
        });
        $body->when(true, function (MultipartBodyRepository $body) {
            $body->add('sidekick', 'Mantas');
        });
        $body->when(false, function (MultipartBodyRepository $body) {
            $body->add('sidekick', 'Teo');
        });

        $this->assertEquals([
            new MultipartValue('name', 'Gareth'),
            new MultipartValue('sidekick', 'Mantas'),
        ], $body->all());
    }

    public function testYouCanDeleteAnItem()
    {
        $body = new MultipartBodyRepository();

        $body->add('name', 'Sam');
        $body->remove('name');

        $this->assertEquals([], $body->all());
    }

    public function testYouCanGetAnItem()
    {
        $body = new MultipartBodyRepository();

        $body->add('name', 'Sam');
        $body->add('friend', 'Chris');

        $this->assertEquals(new MultipartValue('name', 'Sam'), $body->get('name'));
        $this->assertEquals(new MultipartValue('friend', 'Chris'), $body->get('friend'));
    }

    public function testYouCanGetMultipleItemsWithTheSameName()
    {
        $body = new MultipartBodyRepository();

        $body->add('name', 'Sam');
        $body->add('name', 'Alex');

        $this->assertEquals([
            new MultipartValue('name', 'Sam'),
            new MultipartValue('name', 'Alex'),
        ], $body->get('name'));
    }

    public function testYouCanGetAllItems()
    {
        $body = new MultipartBodyRepository();

        $body->add('name', 'Sam');
        $body->add('superhero', 'Iron Man');

        $allResults = [
            new MultipartValue('name', 'Sam'),
            new MultipartValue('superhero', 'Iron Man'),
        ];

        $this->assertEquals($allResults, $body->all());
        $this->assertEquals($allResults, $body->all());
    }

    public function testYouCanMergeItemsTogetherIntoTheBodyRepository()
    {
        $body = new MultipartBodyRepository();

        $this->assertInstanceOf(MergeableBody::class, $body);

        $body->add('name', 'Sam');
        $body->add('sidekick', 'Mantas');

        $body->merge([new MultipartValue('sidekick', 'Gareth')], [new MultipartValue('superhero', 'Black Widow')]);

        $this->assertEquals([
            new MultipartValue('name', 'Sam'),
            new MultipartValue('sidekick', 'Mantas'),
            new MultipartValue('sidekick', 'Gareth'),
            new MultipartValue('superhero', 'Black Widow'),
        ], $body->all());
    }

    public function testItWillThrowAnExceptionIfTheMergedItemsAreNotMultipartValueObjects()
    {
        $body = new MultipartBodyRepository();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value array must only contain Saloon\Data\MultipartValue objects');

        $body->merge([new MultipartValue('sidekick', 'Gareth')], ['superhero' => 'Black Widow']);
    }

    public function testYouCanCheckIfTheStoreIsEmptyOrNot()
    {
        $body = new MultipartBodyRepository();

        $this->assertTrue($body->isEmpty());
        $this->assertFalse($body->isNotEmpty());

        $body->add('name', 'Sam');

        $this->assertFalse($body->isEmpty());
        $this->assertTrue($body->isNotEmpty());
    }
}
