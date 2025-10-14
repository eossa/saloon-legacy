<?php

namespace Saloon\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Saloon\Data\RecordedResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\DTORequest;

class FixtureDataTest extends TestCase
{
    public function testYouCanCreateAFixtureDataObjectFromAFileString()
    {
        $data = [
            'statusCode' => 200,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'data' => [
                'name' => 'Sam',
            ],
        ];

        $fixtureData = RecordedResponse::fromFile(json_encode($data));

        $this->assertEquals($data['statusCode'], $fixtureData->statusCode);
        $this->assertEquals($data['headers'], $fixtureData->headers);
        $this->assertEquals($data['data'], $fixtureData->data);
    }

    public function testYouCanCreateAMockResponseFromFixtureData()
    {
        $data = [
            'statusCode' => 200,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'data' => [
                'name' => 'Sam',
            ],
        ];

        $fixtureData = RecordedResponse::fromFile(json_encode($data));
        $mockResponse = $fixtureData->toMockResponse();

        $this->assertEquals(new MockResponse($data['data'], $data['statusCode'], $data['headers']), $mockResponse);
    }

    public function testYouCanJsonSerializeTheFixtureDataOrConvertItIntoAFile()
    {
        $data = [
            'statusCode' => 200,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'data' => [
                'name' => 'Sam',
            ],
        ];

        $fixtureData = RecordedResponse::fromFile(json_encode($data, JSON_PRETTY_PRINT));

        $serialized = json_encode($fixtureData, JSON_PRETTY_PRINT);

        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $serialized);
        $this->assertEquals($serialized, $fixtureData->toFile());
    }

    public function testArbitraryDataCanBeMergedInTheFixture()
    {
        $response = connector()->send(new DTORequest(), new MockClient([
            MockResponse::fixture('user')->merge([
                'name' => 'Sam Carré',
            ]),
        ]));

        $dto = $response->dto();

        $this->assertEquals('Sam Carré', $dto->name);
        $this->assertEquals('Sam', $dto->actualName);
        $this->assertEquals('@carre_sam', $dto->twitter);
    }

    public function testArbitraryDataUsingDotNotationCanBeMergedInTheFixture()
    {
        $response = connector()->send(new DTORequest(), new MockClient([
            MockResponse::fixture('users')->merge([
                'data.0.twitter' => '@jon_doe',
            ]),
        ]));

        $data = $response->json('data');

        $this->assertCount(2, $data);
        $this->assertEquals('@jon_doe', $data[0]['twitter']);
        $this->assertEquals('@janedoe', $data[1]['twitter']);
    }

    public function testAClosureCanBeUsedToModifyTheMockResponseData()
    {
        $response = connector()->send(new DTORequest(), new MockClient([
            MockResponse::fixture('users')->through(function (array $data) {
                return array_merge_recursive($data, [
                    'data' => [
                        [
                            'name' => 'Sam',
                            'actual_name' => 'Carré',
                            'twitter' => '@carre_sam',
                        ],
                    ],
                ]);
            }),
        ]));

        $data = $response->json('data');

        $this->assertCount(3, $data);
        $this->assertEquals('@jondoe', $data[0]['twitter']);
        $this->assertEquals('@janedoe', $data[1]['twitter']);
        $this->assertEquals('@carre_sam', $data[2]['twitter']);
    }
}
