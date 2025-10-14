<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasConnectorUserRequest;

class RequestTest extends TestCase
{
    public function testARequestCanBeMadeSuccessfully()
    {
        $connector = new TestConnector();
        $response = $connector->send(new UserRequest());

        $data = $response->json();

        $this->assertFalse($response->getPendingRequest()->isAsynchronous());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isMocked());
        $this->assertEquals(200, $response->status());

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $data);
    }

    public function testARequestCanHandleAnExceptionProperly()
    {
        $connector = new TestConnector();
        $response = $connector->send(new ErrorRequest());

        $this->assertFalse($response->isMocked());
        $this->assertEquals(500, $response->status());
    }

    public function testARequestWithHasConnectorCanBeSentIndividually()
    {
        $request = new HasConnectorUserRequest();

        $this->assertInstanceOf(TestConnector::class, $request->connector());
        $this->assertInstanceOf(GuzzleSender::class, $request->sender());
        $this->assertInstanceOf(PendingRequest::class, $request->createPendingRequest());

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
}
