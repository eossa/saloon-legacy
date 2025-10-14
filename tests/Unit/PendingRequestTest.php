<?php

namespace Saloon\Tests\Unit;

use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\InvalidHeaderException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use PHPUnit\Framework\TestCase;

class PendingRequestTest extends TestCase
{
    public function testYouCanOverwriteTheUrlAndTheMethodOfThePendingRequest()
    {
        $connector = new TestConnector();

        $connector->withMockClient(new MockClient([
            new MockResponse(['name' => 'Sam']),
        ]));

        $connector->middleware()->onRequest(function (PendingRequest $pendingRequest) {
            $pendingRequest->setUrl('https://other-endpoint.co.uk' . $pendingRequest->getRequest()->resolveEndpoint());
            $pendingRequest->setMethod(Method::POST);
        });

        $request = new UserRequest();

        $this->assertEquals(Method::GET, $request->getMethod());

        $response = $connector->send(new UserRequest());
        $pendingRequest = $response->getPendingRequest();

        $this->assertEquals('https://other-endpoint.co.uk/user', $pendingRequest->getUrl());
        $this->assertEquals(Method::POST, $pendingRequest->getMethod());
    }

    public function testThePendingRequestIsMacroable()
    {
        PendingRequest::macro('yee', function () {
            return 'haw';
        });

        $pendingRequest = connector()->createPendingRequest(new UserRequest());

        $this->assertEquals('haw', $pendingRequest->yee());
    }

    public function testThePendingRequestValidatesProperlyFormedHeaders()
    {
        $request = new UserRequest();

        $request->headers()->set([
            'Content-Type: application/json',
        ]);

        $this->expectException(InvalidHeaderException::class);
        $this->expectExceptionMessage('One or more of the headers are invalid. Make sure to use the header name as the key. For example: [\'Content-Type\' => \'application/json\'].');

        connector()->createPendingRequest($request);
    }
}
