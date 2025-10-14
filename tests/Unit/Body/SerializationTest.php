<?php

namespace Saloon\Tests\Unit\Body;

use PHPUnit\Framework\TestCase;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;

class SerializationTest extends TestCase
{
    public function testTheJsonBodyRepositoryCanBeEncodedIntoJSON()
    {
        $body = new JsonBodyRepository([
            'name' => 'Sam',
            'sidekick' => 'Mantas',
        ]);

        $this->assertEquals('{"name":"Sam","sidekick":"Mantas"}', (string)$body);
    }

    public function testTheFormBodyRepositoryCanBeEncodedIntoAQueryList()
    {
        $body = new FormBodyRepository([
            'name' => 'Sam',
            'sidekick' => 'Mantas',
        ]);

        $this->assertEquals('name=Sam&sidekick=Mantas', (string)$body);
    }

    public function testTheStringBodyRepositoryCanBeEncodedIntoAString()
    {
        $body = new StringBodyRepository('name: Sam');

        $this->assertEquals('name: Sam', (string)$body);
    }
}
