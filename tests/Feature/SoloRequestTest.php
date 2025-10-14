<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\SoloUserRequest;
use Saloon\Tests\Fixtures\Requests\SoloErrorRequest;

class SoloRequestTest extends TestCase
{
    public function testASoloRequestCanBeSentSynchronously()
    {
        $request = new SoloUserRequest();
        $response = $request->send();

        $data = $response->json();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isMocked());
        $this->assertEquals(200, $response->status());

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $data);
    }

    public function testASynchronousSoloRequestCanHandleAnExceptionProperty()
    {
        $request = new SoloErrorRequest();
        $response = $request->send();

        $this->assertFalse($response->isMocked());
        $this->assertEquals(500, $response->status());
    }

    public function testASoloRequestCanBeSentAsynchronously()
    {
        $request = new SoloUserRequest();
        $promise = $request->sendAsync();

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $response = $promise->wait();

        $data = $response->json();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isMocked());
        $this->assertEquals(200, $response->status());

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $data);
    }

    public function testAAsynchronousSoloRequestCanHandleAnExceptionProperty()
    {
        $request = new SoloErrorRequest();
        $promise = $request->sendAsync();

        $this->expectException(RequestException::class);

        $promise->wait();
    }
}
