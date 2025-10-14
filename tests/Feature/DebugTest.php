<?php

namespace Saloon\Tests\Feature;

use Exception;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Response;
use Saloon\Helpers\Debugger;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\VarDumper;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;

class DebugTest extends TestCase
{
    public function testAUserCanRegisterARequestAndResponseDebuggerOnTheConnectorAndRequest()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sam']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $connectorRequestDebuggerValid = false;
        $connectorResponseDebuggerValid = false;

        $requestClassRequestDebuggerValid = false;
        $requestClassResponseDebuggerValid = false;

        // The connector can register a callback to debug the request

        $connector->debugRequest(function (PendingRequest $pendingRequest, RequestInterface $psrRequest) use (&$connectorRequestDebuggerValid) {
            $this->assertInstanceOf(PendingRequest::class, $pendingRequest);
            $this->assertInstanceOf(RequestInterface::class, $psrRequest);

            $connectorRequestDebuggerValid = true;
        });

        // The connector can register a callback to debug the response

        $connector->debugResponse(function (Response $response, ResponseInterface $psrResponse) use (&$connectorResponseDebuggerValid) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertInstanceOf(ResponseInterface::class, $psrResponse);

            $connectorResponseDebuggerValid = true;
        });

        $request = new UserRequest();

        // The request can register a callback to debug the request

        $request->debugRequest(function (PendingRequest $pendingRequest, RequestInterface $psrRequest) use (&$requestClassRequestDebuggerValid) {
            $this->assertInstanceOf(PendingRequest::class, $pendingRequest);
            $this->assertInstanceOf(RequestInterface::class, $psrRequest);

            $requestClassRequestDebuggerValid = true;
        });

        // The request can register a callback to debug the response

        $request->debugResponse(function (Response $response, ResponseInterface $psrResponse) use (&$requestClassResponseDebuggerValid) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertInstanceOf(ResponseInterface::class, $psrResponse);

            $requestClassResponseDebuggerValid = true;
        });

        $connector->send($request);

        // Check these are all true

        $this->assertTrue($connectorRequestDebuggerValid);
        $this->assertTrue($connectorResponseDebuggerValid);
        $this->assertTrue($requestClassRequestDebuggerValid);
        $this->assertTrue($requestClassResponseDebuggerValid);
    }

    public function testTheResponseDebuggerIsAlwaysExecutedBeforeUserMiddleware()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sam']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $request = new UserRequest();

        $middlewareOrder = [];

        $connector->middleware()->onResponse(function () use (&$middlewareOrder) {
            $middlewareOrder[] = 'A';
        });

        $request->middleware()->onResponse(function () use (&$middlewareOrder) {
            $middlewareOrder[] = 'B';
        });

        $connector->debugResponse(function () use (&$middlewareOrder) {
            $middlewareOrder[] = 'C';
        });

        $request->debugResponse(function () use (&$middlewareOrder) {
            $middlewareOrder[] = 'D';
        });

        $response = $connector->send($request);

        // Even though the user has registered response middleware, the debugger should always come first.

        $this->assertEquals(['C', 'D', 'A', 'B'], $middlewareOrder);
    }

    public function testTheResponseDebuggerIsAlwaysExecutedBeforeTheAlwaysThrowOnErrorsTrait()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sam'], 500),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $request = new AlwaysThrowRequest();

        $middlewareCount = 0;

        $connector->debugResponse(function () use (&$middlewareCount) {
            $middlewareCount++;
        });

        $request->debugResponse(function () use (&$middlewareCount) {
            $middlewareCount++;
        });

        try {
            $connector->send($request);
        } catch (Exception $exception) {
            //
        }

        $this->assertEquals(2, $middlewareCount);
    }

    public function testTheDefaultDebugRequestDriverWillDumpAnOutputUsingSymfonyVarDumper()
    {
        $output = fopen('php://memory', 'rwb+');

        VarDumper::setHandler(getCustomVarDump($output));

        $connector = new TestConnector();

        $connector->withMockClient(new MockClient([
            new MockResponse(['name' => 'Sam'], 500),
        ]));

        $connector->debugRequest()->send(new UserRequest());

        VarDumper::setHandler(null);

        rewind($output);

        $output = stream_get_contents($output);

        $expected = "array:6 [\n" .
                   "  \"connector\" => \"Saloon\\Tests\\Fixtures\\Connectors\\TestConnector\"\n" .
                   "  \"request\" => \"Saloon\\Tests\\Fixtures\\Requests\\UserRequest\"\n" .
                   "  \"method\" => \"GET\"\n" .
                   "  \"uri\" => \"https://tests.saloon.dev/api/user\"\n" .
                   "  \"headers\" => array:2 [\n" .
                   "    \"Host\" => \"tests.saloon.dev\"\n" .
                   "    \"Accept\" => \"application/json\"\n" .
                   "  ]\n" .
                   "  \"body\" => \"\"\n" .
                   "]\n";

        $this->assertEquals(str_replace("\r\n", "\n", $expected), $output);
    }

    public function testTheDefaultDebugResponseDriverWillDumpAnOutputUsingSymfonyVarDumper()
    {
        $output = fopen('php://memory', 'rwb+');

        VarDumper::setHandler(getCustomVarDump($output));

        $connector = new TestConnector();

        $connector->withMockClient(new MockClient([
            new MockResponse(['name' => 'Sam'], 500),
        ]));

        $connector->debugResponse()->send(new UserRequest());

        VarDumper::setHandler(null);

        rewind($output);

        $output = stream_get_contents($output);

        $expected = "array:3 [\n" .
                   "  \"status\" => 500\n" .
                   "  \"headers\" => []\n" .
                   "  \"body\" => \"{\"name\":\"Sam\"}\"\n" .
                   "]\n";

        $this->assertEquals(str_replace("\r\n", "\n", $expected), $output);
    }

    public function testTheDebugMethodWillOutputBothRequestAndResponseAtTheSameTime()
    {
        $output = fopen('php://memory', 'rwb+');

        VarDumper::setHandler(getCustomVarDump($output));

        $connector = new TestConnector();

        $connector->withMockClient(new MockClient([
            new MockResponse(['name' => 'Sam'], 500),
        ]));

        $connector->debug()->send(new UserRequest());

        VarDumper::setHandler(null);

        rewind($output);

        $output = stream_get_contents($output);

        $expected = "array:6 [\n" .
                   "  \"connector\" => \"Saloon\\Tests\\Fixtures\\Connectors\\TestConnector\"\n" .
                   "  \"request\" => \"Saloon\\Tests\\Fixtures\\Requests\\UserRequest\"\n" .
                   "  \"method\" => \"GET\"\n" .
                   "  \"uri\" => \"https://tests.saloon.dev/api/user\"\n" .
                   "  \"headers\" => array:2 [\n" .
                   "    \"Host\" => \"tests.saloon.dev\"\n" .
                   "    \"Accept\" => \"application/json\"\n" .
                   "  ]\n" .
                   "  \"body\" => \"\"\n" .
                   "]\n" .
                   "array:3 [\n" .
                   "  \"status\" => 500\n" .
                   "  \"headers\" => []\n" .
                   "  \"body\" => \"{\"name\":\"Sam\"}\"\n" .
                   "]\n";

        $this->assertEquals(str_replace("\r\n", "\n", $expected), $output);
    }

    public function testTheDebugMethodCanKillTheApplication()
    {
        $killed = false;

        $output = fopen('php://memory', 'rwb+');

        VarDumper::setHandler(getCustomVarDump($output));

        Debugger::$dieHandler = static function () use (&$killed) {
            $killed = true;
        };

        $connector = new TestConnector();

        $connector->withMockClient(new MockClient([
            new MockResponse(['name' => 'Sam'], 500),
        ]));

        $connector->debug(true)->send(new UserRequest());

        VarDumper::setHandler(null);
        Debugger::$dieHandler = null;

        $this->assertTrue($killed);
    }
}
