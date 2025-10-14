<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Uri;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\ModifiedPsrUserRequest;
use Saloon\Tests\Fixtures\Connectors\ModifiedPsrRequestConnector;

class PsrTest extends TestCase
{
    public function testTheConnectorAndRequestCanModifyThePsrRequestWhenItIsCreated()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $connector = new ModifiedPsrRequestConnector();
        $connector->withMockClient($mockClient);

        $response = $connector->send(new ModifiedPsrUserRequest());

        // The connector will change the URI to https://google.com

        $this->assertEquals(new Uri('https://google.com'), $response->getPsrRequest()->getUri());

        // The request will add the X-Howdy header

        $this->assertArrayHasKey('X-Howdy', $response->getPsrRequest()->getHeaders());
        $this->assertEquals(['Yeehaw'], $response->getPsrRequest()->getHeaders()['X-Howdy']);
    }
}
