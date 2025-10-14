<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;
use PHPUnit\Framework\TestCase;

class MockResponseTest extends TestCase
{
    public function testPullingAResponseFromTheSequenceWillReturnTheCorrectResponse()
    {
        $responseA = MockResponse::make();
        $responseB = MockResponse::make([], 500);
        $responseC = MockResponse::make([], 500);

        $mockClient = new MockClient([$responseA, $responseB, $responseC]);

        $this->assertEquals($responseA->status(), $mockClient->getNextFromSequence()->status());
        $this->assertEquals($responseB->status(), $mockClient->getNextFromSequence()->status());
        $this->assertEquals($responseC->status(), $mockClient->getNextFromSequence()->status());
        $this->assertTrue($mockClient->isEmpty());
    }

    public function testAMockResponseCanHaveRawBodyData()
    {
        $response = MockResponse::make('xml', 200, ['Content-Type' => 'application/json']);

        $this->assertEquals(['Content-Type' => 'application/json'], $response->headers()->all());
        $this->assertEquals(200, $response->status());
        $this->assertInstanceOf(StringBodyRepository::class, $response->body());
        $this->assertEquals('xml', $response->body()->all());
    }

    public function testAResponseCanBeACustomResponseClass()
    {
        $mockClient = new MockClient([MockResponse::make(['foo' => 'bar'])]);
        $request = new UserRequestWithCustomResponse();

        $response = connector()->send($request, $mockClient);

        $this->assertInstanceOf(UserResponse::class, $response);
        $this->assertInstanceOf(UserData::class, $response->customCastMethod());
        $this->assertEquals('bar', $response->foo());
    }
}
