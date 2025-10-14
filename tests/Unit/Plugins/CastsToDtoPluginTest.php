<?php

namespace Saloon\Tests\Unit\Plugins;

use LogicException;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Data\ApiResponse;
use Saloon\Tests\Fixtures\Requests\DTORequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\DtoConnector;

class CastsToDtoPluginTest extends TestCase
{
    public function testItCanCastToADtoThatIsDefinedOnTheRequest()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
        ]);

        $response = connector()->send(new DTORequest(), $mockClient);
        $dto = $response->dto();
        $json = $response->json();

        $this->assertTrue($response->isMocked());
        $this->assertInstanceOf(User::class, $dto);
        $this->assertEquals($json['name'], $dto->name);
        $this->assertEquals($json['actual_name'], $dto->actualName);
        $this->assertEquals($json['twitter'], $dto->twitter);
    }

    public function testItCanCastToADtoThatIsDefinedOnAConnector()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
        ]);

        $connector = new DtoConnector();

        $response = $connector->send(new UserRequest(), $mockClient);
        $dto = $response->dto();

        $this->assertInstanceOf(ApiResponse::class, $dto);
        $this->assertEquals($response->json(), $dto->data);
    }

    public function testTheRequestDtoWillBeReturnedAsAHigherPriorityThanTheConnectorDto()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
        ]);

        $connector = new DtoConnector();

        $response = $connector->send(new DTORequest(), $mockClient);
        $dto = $response->dto();
        $json = $response->json();

        $this->assertInstanceOf(User::class, $dto);
        $this->assertEquals($json['name'], $dto->name);
        $this->assertEquals($json['actual_name'], $dto->actualName);
        $this->assertEquals($json['twitter'], $dto->twitter);
    }

    public function testYouCanUseTheDtoOrFailMethodToThrowAnExceptionIfTheResponseHasFailed()
    {
        $mockClient = new MockClient([
            new MockResponse(['message' => 'Server Error'], 500),
        ]);

        $response = connector()->send(new DTORequest(), $mockClient);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unable to create data transfer object as the response has failed.');

        $response->dtoOrFail();
    }
}
