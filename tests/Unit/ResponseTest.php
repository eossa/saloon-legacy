<?php

namespace Saloon\Tests\Unit;

use Saloon\XmlReader\XmlReader;
use SimpleXMLElement;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\Response;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\ArrayStore;
use Illuminate\Support\Collection;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\DomCrawler\Crawler;
use Saloon\Http\Response as SaloonResponse;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\CustomEndpointRequest;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testYouCanGetTheOriginalPendingRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $pendingRequest = $response->getPendingRequest();

        $this->assertInstanceOf(PendingRequest::class, $pendingRequest);
        $this->assertInstanceOf(UserRequest::class, $pendingRequest->getRequest());
    }

    public function testYouCanGetTheConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $request = new UserRequest();
        $connector = new TestConnector();
        $response = $connector->send($request, $mockClient);

        $this->assertSame($connector, $response->getConnector());
    }

    public function testYouCanGetTheOriginalRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $request = new UserRequest();
        $response = connector()->send($request, $mockClient);

        $this->assertSame($request, $response->getRequest());
    }

    public function testYouCanGetThePsr7Request()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $request = new UserRequest();
        $response = connector()->send($request, $mockClient);

        $this->assertInstanceOf(RequestInterface::class, $response->getPsrRequest());
    }

    public function testItWillThrowAnExceptionWhenYouUseTheThrowMethod()
    {
        $mockClient = new MockClient([
            MockResponse::make([], 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->expectException(RequestException::class);

        $response->throwException();
    }

    public function testItWontThrowAnExceptionIfTheRequestDidNotFail()
    {
        $mockClient = new MockClient([
            MockResponse::make([], 200),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertSame($response, $response->throwException());
    }

    public function testToExceptionWillReturnASaloonRequestException()
    {
        $mockClient = new MockClient([
            MockResponse::make([], 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);
        $exception = $response->toException();

        $this->assertInstanceOf(RequestException::class, $exception);
    }

    public function testToExceptionWontReturnAnythingIfTheRequestDidNotFail()
    {
        $mockClient = new MockClient([
            MockResponse::make([], 200),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);
        $exception = $response->toException();

        $this->assertNull($exception);
    }

    public function testTheOnErrorMethodWillRunACustomClosure()
    {
        $mockClient = new MockClient([
            MockResponse::make([], 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);
        $count = 0;

        $response->onError(function () use (&$count) {
            $count++;
        });

        $this->assertEquals(1, $count);
    }

    public function testTheObjectMethodWillReturnAnObject()
    {
        $data = ['name' => 'Sam', 'work' => 'Codepotato'];

        $mockClient = new MockClient([
            MockResponse::make($data, 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $dataAsObject = (object)$data;

        $this->assertEquals($dataAsObject, $response->object());
    }

    public function testTheObjectMethodWithADotNotationKeyValueWillReturnANestedString()
    {
        $data = [
            'contacts' => [
                ['name' => 'Sam', 'work' => 'Codepotato'],
                ['name' => 'Braunson', 'work' => 'Geekybeaver'],
            ],
        ];

        $mockClient = new MockClient([
            MockResponse::make($data, 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals('Braunson', $response->object('contacts.1.name'));
    }

    public function testTheCollectMethodWillReturnACollection()
    {
        if (!class_exists(Collection::class)) {
            $this->markTestSkipped('This test requires the illuminate/support package.');
        }
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);
        $collection = $response->collect();

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertEquals('Sam', $collection['name']);
        $this->assertEquals('Codepotato', $collection['work']);

        $this->assertEquals(['Sam'], $response->collect('name')->toArray());
        $this->assertTrue($response->collect('age')->isEmpty());
    }

    public function testTheJsonMethodWillReturnEmptyArrayIfBodyIsEmpty()
    {
        $mockClient = new MockClient([
            MockResponse::make('', 404),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals([], $response->json());
    }

    public function testTheToPsrResponseMethodWillReturnAGuzzleResponse()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 500),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertInstanceOf(Response::class, $response->getPsrResponse());
    }

    public function testYouCanGetAnIndividualHeaderFromTheResponse()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 200, ['X-Greeting' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals('Howdy', $response->header('X-Greeting'));
        $this->assertEmpty($response->header('X-Missing'));
    }

    public function testItWillConvertTheBodyToStringIfTheCastIsUsed()
    {
        $data = ['name' => 'Sam', 'work' => 'Codepotato'];

        $mockClient = new MockClient([
            MockResponse::make($data, 200, ['X-Greeting' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals(json_encode($data), (string)$response);
    }

    public function testItChecksStatusesCorrectly()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 200, ['X-Greeting' => 'Howdy']),
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 500, ['X-Greeting' => 'Howdy']),
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 302, ['X-Greeting' => 'Howdy']),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseA->successful());
        $this->assertTrue($responseA->ok());
        $this->assertFalse($responseA->redirect());
        $this->assertFalse($responseA->failed());
        $this->assertFalse($responseA->serverError());

        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseB->successful());
        $this->assertFalse($responseB->ok());
        $this->assertFalse($responseB->redirect());
        $this->assertTrue($responseB->failed());
        $this->assertTrue($responseB->serverError());

        $responseC = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseC->successful());
        $this->assertFalse($responseC->ok());
        $this->assertTrue($responseC->redirect());
        $this->assertFalse($responseC->failed());
        $this->assertFalse($responseC->serverError());
    }

    public function testTheXmlMethodWillReturnXmlAsAnArray()
    {
        $mockClient = new MockClient([
            new MockResponse('<SaveContactResponse xmlns="http://schemas.datacontract.org/2004/07/SmashFly.WebServices.ContactManagerService.v2"><ContactId>1168255</ContactId><Errors nil="true" xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays"/><HasErrors>false</HasErrors></SaveContactResponse>', 200),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);
        $simpleXml = $response->xml();

        $this->assertInstanceOf(SimpleXMLElement::class, $simpleXml);
    }

    public function testTheXmlReaderMethodWillReturnAnXmlReaderInstance()
    {
        if (!class_exists(XmlReader::class)) {
            $this->markTestSkipped('This test requires the saloon/xml-reader package.');
        }
        $mockClient = new MockClient([
            MockResponse::fixture('xml'),
        ]);

        $request = new CustomEndpointRequest();
        $request->setEndpoint('/breakfast-menu');

        $response = connector()->send($request, $mockClient);
        $reader = $response->xmlReader();

        $this->assertEquals('Berry-Berry Belgian Waffles', $reader->value('food.2.name')->sole());
    }

    public function testTheHeadersMethodReturnsAnArrayStore()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 200, ['X-Greeting' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertInstanceOf(ArrayStore::class, $response->headers());
    }

    public function testHeadersWithASingleValueWillHaveJustTheStringValueButHeadersWithMultipleValuesWillBeAnArray()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam', 'work' => 'Codepotato'], 200, ['X-Greeting' => 'Howdy', 'X-Farewell' => ['Goodbye', 'Sam']]),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals('Howdy', $response->headers()->get('X-Greeting'));
        $this->assertEquals(['Goodbye', 'Sam'], $response->headers()->get('X-Farewell'));

        $this->assertEquals('Howdy', $response->header('X-Greeting'));
        $this->assertEquals(['Goodbye', 'Sam'], $response->header('X-Farewell'));
    }

    public function testTheDomMethodWillReturnACrawlerInstance()
    {
        $dom = '<p>Howdy <i>Partner</i></p>';

        $mockClient = new MockClient([
            new MockResponse($dom),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertInstanceOf(Crawler::class, $response->dom());
        $this->assertEquals(new Crawler($dom), $response->dom());
    }

    public function testWhenUsingTheBodyMethodsTheStreamIsRewoundBackToTheStart()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals(['foo' => 'bar'], $response->json());
        $this->assertEquals(['foo' => 'bar'], $response->toArray());
        $this->assertEquals('{"foo":"bar"}', $response->body());
        $this->assertEquals('{"foo":"bar"}', stream_get_contents($response->getRawStream()));
        $this->assertEquals((object)['foo' => 'bar'], $response->object());
    }

    public function testItCanConvertTheResponseToADataUrl()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['Content-Type' => 'application/json;encoding=utf-8']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals('data:application/json;encoding=utf-8;base64,eyJmb28iOiJiYXIifQ==', $response->dataUrl());
    }

    public function testIfAResponseIsChangedThroughMiddlewareTheNewInstanceIsUsed()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $connector = new TestConnector();

        $connector->middleware()->onResponse(function (SaloonResponse $response) {
            // Let's modify the body while sending!
            $psrResponse = $response->getPsrResponse();
            $newPsrResponse = $psrResponse->withBody(Utils::streamFor('Hello World!'));

            return $response::fromPsrResponse($newPsrResponse, $response->getPendingRequest(), $response->getPsrRequest());
        });

        $response = $connector->send(new UserRequest(), $mockClient);

        $this->assertEquals('Hello World!', $response->body());
        $this->assertEquals(['X-Custom-Header' => 'Howdy'], $response->headers()->all());
    }

    public function testYouCanGetTheResponseStreamAsARawResource()
    {
        $response = connector()->send(new UserRequest());

        $resource = $response->getRawStream();

        $this->assertTrue(is_resource($resource));

        $this->assertEquals('{"name":"Sammyjo20","actual_name":"Sam","twitter":"@carre_sam"}', stream_get_contents($resource));
    }

    public function testYouCanGetTheResponseStreamAsARawResourceWithAMockResponse()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $resource = $response->getRawStream();

        $this->assertTrue(is_resource($resource));

        $this->assertEquals('{"foo":"bar"}', stream_get_contents($resource));
    }

    /**
     * @dataProvider saveBodyToFileProvider
     */
    public function testYouCanSaveTheResponseToAFile($resourceOrPath)
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);
        $response->saveBodyToFile($resourceOrPath);

        if (is_string($resourceOrPath)) {
            $path = 'tests/Fixtures/Saloon/Testing/streamToFile1.json';
        } else {
            $path = 'tests/Fixtures/Saloon/Testing/streamToFile2.json';
        }

        $this->assertEquals('{"foo":"bar"}', file_get_contents($path));
    }

    public function saveBodyToFileProvider()
    {
        return [
            'string_path' => ['tests/Fixtures/Saloon/Testing/streamToFile1.json'],
            'resource' => [fopen('tests/Fixtures/Saloon/Testing/streamToFile2.json', 'wb+')],
        ];
    }

    public function testTheResponseIsMacroable()
    {
        SaloonResponse::macro('yee', function () {
            return 'haw';
        });

        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
        ]);

        $response = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals('haw', $response->yee());
    }
}
