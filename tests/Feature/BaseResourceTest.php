<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\ResourceConnector;

class BaseResourceTest extends TestCase
{
    public function testAResourceCanBeUsedToSendARequest()
    {
        $mockClient = new MockClient([
            MockResponse::fixture('user'),
        ]);

        $connector = new ResourceConnector();
        $connector->withMockClient($mockClient);

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $connector->user()->get());
    }
}
