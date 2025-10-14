<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Tests\Fixtures\Requests\DTORequest;
use Saloon\Tests\Fixtures\Data\UserWithResponse;
use Saloon\Tests\Fixtures\Requests\DTOWithResponseRequest;

class DataObjectWrapperTest extends TestCase
{
    public function testIfADtoDoesNotImplementTheWithResponseInterfaceAndHasResponseTraitSaloonWillNotAddTheOriginalResponse()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam', 'twitter' => '@carre_sam']),
        ]);

        $response = connector()->send(new DTORequest(), $mockClient);
        $dto = $response->dto();

        $this->assertInstanceOf(User::class, $dto);
        $this->assertNotInstanceOf(WithResponse::class, $dto);
    }

    public function testIfADtoImplementsTheWithResponseInterfaceAndHasResponseTraitSaloonWillAddTheOriginalResponse()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam', 'twitter' => '@carre_sam']),
        ]);

        $request = new DTOWithResponseRequest();
        $response = connector()->send($request, $mockClient);

        /** @var UserWithResponse $dto */
        $dto = $response->dto();

        $this->assertInstanceOf(UserWithResponse::class, $dto);
        $this->assertInstanceOf(WithResponse::class, $dto);
        $this->assertSame($response, $dto->getResponse());
    }
}
