<?php

namespace Saloon\Tests\Feature;

use Exception;
use PHPUnit\Framework\TestCase;
use Saloon\MockConfig;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\FixtureException;
use Saloon\Tests\Fixtures\Mocking\UserFixture;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Mocking\SafeUserFixture;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Mocking\RegexUserFixture;
use Saloon\Tests\Fixtures\Mocking\SuperheroFixture;
use Saloon\Tests\Fixtures\Mocking\MissingNameFixture;
use Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;
use Saloon\Tests\Fixtures\Mocking\CallableMockResponse;
use Saloon\Tests\Fixtures\Requests\FileDownloadRequest;
use Saloon\Tests\Fixtures\Mocking\BeforeSaveUserFixture;
use Saloon\Tests\Fixtures\Requests\PagedSuperheroRequest;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Tests\Fixtures\Connectors\DifferentServiceConnector;
use Saloon\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

class MockRequestTest extends TestCase
{
    private $filesystem;

    protected function setUp()
    {
        parent::setUp();

        $this->filesystem = new Filesystem(new Local('tests/Fixtures/Saloon/Testing'));

        MockConfig::setFixturePath('tests/Fixtures/Saloon/Testing');

        $content = $this->filesystem->listContents('/', true);
        foreach ($content as $file) {
            if ($file['type'] === 'dir') {
                $this->filesystem->deleteDir($file['path']);
            } elseif ($file['type'] === 'file' && $this->filesystem->has($file['path'])) {
                $this->filesystem->delete($file['path']);
            }
        }
    }

    protected function tearDown()
    {
        MockConfig::setFixturePath('tests/Fixtures/Saloon');

        parent::tearDown();
    }

    public function testARequestCanBeMockedWithASequence()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 200, ['X-Foo' => 'Bar']),
            MockResponse::make(['name' => 'Alex']),
            MockResponse::make(['error' => 'Server Unavailable'], 500),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $responseA = $connector->send(new UserRequest());

        $this->assertInstanceOf(Response::class, $responseA);
        $this->assertTrue($responseA->isMocked());
        $this->assertTrue($responseA->isFaked());
        $this->assertFalse($responseA->isCached());
        $this->assertEquals(['name' => 'Sam'], $responseA->json());
        $this->assertEquals(200, $responseA->status());
        $this->assertInstanceOf(MockResponse::class, $responseA->getFakeResponse());
        $this->assertEquals(['X-Foo' => 'Bar'], $responseA->headers()->all());

        $responseB = $connector->send(new UserRequest());

        $this->assertInstanceOf(Response::class, $responseB);
        $this->assertTrue($responseB->isMocked());
        $this->assertTrue($responseB->isFaked());
        $this->assertFalse($responseB->isCached());
        $this->assertEquals(['name' => 'Alex'], $responseB->json());
        $this->assertEquals(200, $responseB->status());
        $this->assertInstanceOf(MockResponse::class, $responseB->getFakeResponse());

        $responseC = $connector->send(new UserRequest());

        $this->assertInstanceOf(Response::class, $responseC);
        $this->assertTrue($responseC->isMocked());
        $this->assertTrue($responseC->isFaked());
        $this->assertFalse($responseC->isCached());
        $this->assertEquals(['error' => 'Server Unavailable'], $responseC->json());
        $this->assertEquals(500, $responseC->status());
        $this->assertInstanceOf(MockResponse::class, $responseC->getFakeResponse());

        $this->expectException(NoMockResponseFoundException::class);
        $this->expectExceptionMessage('Saloon was unable to guess a mock response for your request [https://tests.saloon.dev/api/user], consider using a wildcard url mock or a connector mock.');

        $connector->send(new UserRequest());
    }

    public function testARequestCanBeMockedWithAConnectorDefined()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);

        $connectorA = new TestConnector();
        $connectorB = new QueryParameterConnector();

        $connectorARequest = new UserRequest();
        $connectorBRequest = new QueryParameterConnectorRequest();

        $mockClient = new MockClient([
            TestConnector::class => $responseA,
            QueryParameterConnector::class => $responseB,
        ]);

        $responseA = $connectorA->send($connectorARequest, $mockClient);

        $this->assertTrue($responseA->isMocked());
        $this->assertEquals(['name' => 'Sammyjo20'], $responseA->json());
        $this->assertEquals(200, $responseA->status());

        $responseB = $connectorB->send($connectorBRequest, $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(['name' => 'Alex'], $responseB->json());
        $this->assertEquals(200, $responseB->status());
    }

    public function testARequestCanBeMockedWithARequestDefined()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);

        $connectorA = new TestConnector();
        $connectorB = new QueryParameterConnector();

        $requestA = new UserRequest();
        $requestB = new QueryParameterConnectorRequest();

        $mockClient = new MockClient([
            UserRequest::class => $responseA,
            QueryParameterConnectorRequest::class => $responseB,
        ]);

        $responseA = $connectorA->send($requestA, $mockClient);

        $this->assertTrue($responseA->isMocked());
        $this->assertEquals(['name' => 'Sammyjo20'], $responseA->json());
        $this->assertEquals(200, $responseA->status());

        $responseB = $connectorB->send($requestB, $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(['name' => 'Alex'], $responseB->json());
        $this->assertEquals(200, $responseB->status());
    }

    public function testARequestCanBeMockedWithAUrlDefined()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);
        $responseC = MockResponse::make(['error' => 'Server Broken'], 500);

        $connectorA = new TestConnector();
        $connectorB = new DifferentServiceConnector();

        $requestA = new UserRequest();
        $requestB = new ErrorRequest();
        $requestC = new DifferentServiceUserRequest();

        $mockClient = new MockClient([
            'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
            'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
            'google.com/*' => $responseC, // Test Different Route,
        ]);

        $responseA = $connectorA->send($requestA, $mockClient);

        $this->assertTrue($responseA->isMocked());
        $this->assertEquals(['name' => 'Sammyjo20'], $responseA->json());
        $this->assertEquals(200, $responseA->status());

        $responseB = $connectorA->send($requestB, $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(['name' => 'Alex'], $responseB->json());
        $this->assertEquals(200, $responseB->status());

        $responseC = $connectorB->send($requestC, $mockClient);

        $this->assertTrue($responseC->isMocked());
        $this->assertEquals(['error' => 'Server Broken'], $responseC->json());
        $this->assertEquals(500, $responseC->status());
    }

    public function testYouCanCreateWildcardUrlMocks()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);
        $responseC = MockResponse::make(['error' => 'Server Broken'], 500);

        $connectorA = new TestConnector();
        $connectorB = new DifferentServiceConnector();

        $requestA = new UserRequest();
        $requestB = new ErrorRequest();
        $requestC = new DifferentServiceUserRequest();

        $mockClient = new MockClient([
            'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
            'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
            '*' => $responseC,
        ]);

        $responseA = $connectorA->send($requestA, $mockClient);

        $this->assertTrue($responseA->isMocked());
        $this->assertEquals(['name' => 'Sammyjo20'], $responseA->json());
        $this->assertEquals(200, $responseA->status());

        $responseB = $connectorA->send($requestB, $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(['name' => 'Alex'], $responseB->json());
        $this->assertEquals(200, $responseB->status());

        $responseC = $connectorB->send($requestC, $mockClient);

        $this->assertTrue($responseC->isMocked());
        $this->assertEquals(['error' => 'Server Broken'], $responseC->json());
        $this->assertEquals(500, $responseC->status());
    }

    public function testYouCanUseAClosureForTheMockResponse()
    {
        $sequenceMock = new MockClient([
            function (PendingRequest $pendingRequest) {
                return new MockResponse(['request' => $pendingRequest->getUrl()]);
            },
        ]);

        $sequenceResponse = connector()->send(new UserRequest(), $sequenceMock);

        $this->assertTrue($sequenceResponse->isMocked());
        $this->assertEquals(['request' => 'https://tests.saloon.dev/api/user'], $sequenceResponse->json());

        // Connector mock

        $connectorMock = new MockClient([
            TestConnector::class => function (PendingRequest $pendingRequest) {
                return new MockResponse(['request' => $pendingRequest->getUrl()]);
            },
        ]);

        $connectorResponse = connector()->send(new UserRequest(), $connectorMock);

        $this->assertTrue($connectorResponse->isMocked());
        $this->assertEquals(['request' => 'https://tests.saloon.dev/api/user'], $connectorResponse->json());

        // Request mock

        $requestMock = new MockClient([
            UserRequest::class => function (PendingRequest $pendingRequest) {
                return new MockResponse(['request' => $pendingRequest->getUrl()]);
            },
        ]);

        $requestResponse = connector()->send(new UserRequest(), $requestMock);

        $this->assertTrue($requestResponse->isMocked());
        $this->assertEquals(['request' => 'https://tests.saloon.dev/api/user'], $requestResponse->json());

        // URL mock

        $urlMock = new MockClient([
            'tests.saloon.dev/*' => function (PendingRequest $pendingRequest) {
                return new MockResponse(['request' => $pendingRequest->getUrl()]);
            },
        ]);

        $urlResponse = connector()->send(new UserRequest(), $urlMock);

        $this->assertTrue($urlResponse->isMocked());
        $this->assertEquals(['request' => 'https://tests.saloon.dev/api/user'], $urlResponse->json());
    }

    public function testYouCanUseACallableClassAsTheMockResponse()
    {
        $mockClient = new MockClient([
            UserRequest::class => new CallableMockResponse(),
        ]);

        $sequenceResponse = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($sequenceResponse->isMocked());
        $this->assertEquals(['request_class' => UserRequest::class], $sequenceResponse->json());
    }

    public function testAFixtureCanBeUsedWithAMockSequence()
    {
        $mockClient = new MockClient([
            MockResponse::fixture('user'),
            MockResponse::fixture('user'),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(200, $responseB->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseB->json());
    }

    public function testAFixtureCanBeUsedWithAConnectorMock()
    {
        $mockClient = new MockClient([
            TestConnector::class => MockResponse::fixture('connector'),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(200, $responseB->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseB->json());

        // Even though it's a different request, it should use the same fixture

        $responseC = connector()->send(new ErrorRequest(), $mockClient);

        $this->assertTrue($responseC->isMocked());
        $this->assertEquals(200, $responseC->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseC->json());
    }

    public function testAFixtureCanBeUsedWithARequestMock()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::fixture('user'),
        ]);

        $this->assertFalse($this->filesystem->has('user.json'));

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $this->assertTrue($this->filesystem->has('user.json'));

        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(200, $responseB->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseB->json());
    }

    public function testAFixtureCanBeUsedWithAUrlMock()
    {
        $mockClient = new MockClient([
            'tests.saloon.dev/api/user' => MockResponse::fixture('user'), // Test Exact Route
            'tests.saloon.dev/*' => MockResponse::fixture('other'), // Test Wildcard Routes
        ]);

        $this->assertFalse($this->filesystem->has('user.json'));
        $this->assertFalse($this->filesystem->has('other.json'));

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($this->filesystem->has('user.json'));
        $this->assertFalse($this->filesystem->has('other.json'));

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $responseB = connector()->send(new ErrorRequest(), $mockClient);

        $this->assertTrue($this->filesystem->has('user.json'));
        $this->assertTrue($this->filesystem->has('other.json'));

        $this->assertFalse($responseB->isMocked());
        $this->assertEquals(500, $responseB->status());
        $this->assertEquals([
            'message' => 'Fake Error',
        ], $responseB->json());

        // This should use the first mock

        $responseC = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseC->isMocked());
        $this->assertEquals(200, $responseC->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseC->json());

        // Another error request should use the "other" mock

        $responseD = connector()->send(new ErrorRequest(), $mockClient);

        $this->assertTrue($responseD->isMocked());
        $this->assertEquals(500, $responseD->status());
        $this->assertEquals([
            'message' => 'Fake Error',
        ], $responseD->json());
    }

    public function testAFixtureCanBeUsedWithAWildcardUrlMock()
    {
        $mockClient = new MockClient([
            '*' => MockResponse::fixture('user'), // Test Exact Route
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $responseB = connector()->send(new ErrorRequest(), $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(200, $responseB->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseB->json());
    }

    public function testAFixtureCanBeUsedWithinAClosureMock()
    {
        $mockClient = new MockClient([
            '*' => function (PendingRequest $pendingRequest) {
                if ($pendingRequest->getRequest() instanceof UserRequest) {
                    return MockResponse::fixture('user');
                }

                return MockResponse::fixture('other');
            },
        ]);

        $this->assertFalse($this->filesystem->has('user.json'));
        $this->assertFalse($this->filesystem->has('other.json'));

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(200, $responseB->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseB->json());

        // Now we'll test a different route

        $responseC = connector()->send(new ErrorRequest(), $mockClient);

        $this->assertFalse($responseC->isMocked());
        $this->assertEquals(500, $responseC->status());
        $this->assertEquals([
            'message' => 'Fake Error',
        ], $responseC->json());

        // Another error request should use the "other" mock

        $responseD = connector()->send(new ErrorRequest(), $mockClient);

        $this->assertTrue($responseD->isMocked());
        $this->assertEquals(500, $responseD->status());
        $this->assertEquals([
            'message' => 'Fake Error',
        ], $responseD->json());
    }

    public function testWhenUsingTheAlwaysThrowRequestTraitTheResponseRecorderWillStillRecordTheResponse()
    {
        $mockClient = new MockClient([
            AlwaysThrowRequest::class => MockResponse::fixture('error'),
        ]);

        $exception = null;

        try {
            connector()->send(new AlwaysThrowRequest(), $mockClient);
        } catch (Exception $exception) {
            //
        }

        $this->assertInstanceOf(RequestException::class, $exception);

        $fixture = MockResponse::fixture('error')->getMockResponse();

        $this->assertInstanceOf(MockResponse::class, $fixture);
    }

    public function testAFixtureCanRecordTheFileDataFromARequestThatReturnsAFileDownload()
    {
        $mockClient = new MockClient([
            FileDownloadRequest::class => MockResponse::fixture('file'),
        ]);

        $requestA = new FileDownloadRequest();
        $responseA = connector()->send($requestA, $mockClient);

        $this->assertEquals(file_get_contents('tests/Fixtures/Files/test.pdf'), $responseA->body());

        $requestB = new FileDownloadRequest();
        $responseB = connector()->send($requestB, $mockClient);

        $this->assertEquals(file_get_contents('tests/Fixtures/Files/test.pdf'), $responseB->body());
    }

    public function testYouCanCreateACustomFixtureClass()
    {
        $mockClient = new MockClient([
            new UserFixture(),
            new UserFixture(),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->isMocked());
        $this->assertEquals(200, $responseA->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseB->isMocked());
        $this->assertEquals(200, $responseB->status());
        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseB->json());
    }

    public function testItWillThrowAnExceptionIfTheCustomFixtureClassIsMissingAName()
    {
        $mockClient = new MockClient([
            new MissingNameFixture(),
        ]);

        $this->expectException(FixtureException::class);
        $this->expectExceptionMessage('The fixture must have a name');

        connector()->send(new UserRequest(), $mockClient);
    }

    public function testYouCanHideSensitiveJsonBodyParametersAndHeadersBeforeTheFixtureIsStored()
    {
        $mockClient = new MockClient([
            new SafeUserFixture(),
            new SafeUserFixture(),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);
        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $this->assertEquals('cloudflare', $responseA->header('Server'));
        $this->assertEquals('no-cache, private', $responseA->header('Cache-Control'));

        $this->assertFalse($responseA->isFaked());
        $this->assertTrue($responseB->isFaked());

        $this->assertEquals([
            'name' => 'Sxxx',
            'actual_name' => 'REDACTED',
            'twitter' => '@saloonphp',
        ], $responseB->json());

        $this->assertEquals('secret', $responseB->header('Server'));
        $this->assertEquals('no-cache, private, yeehaw', $responseB->header('Cache-Control'));

        $fixtureData = json_decode(file_get_contents('tests/Fixtures/Saloon/Testing/user.json'), true);

        $this->assertEquals('secret', $fixtureData['headers']['Server']);
        $this->assertEquals('no-cache, private, yeehaw', $fixtureData['headers']['Cache-Control']);
        $this->assertEquals(json_encode([
            'name' => 'Sxxx',
            'actual_name' => 'REDACTED',
            'twitter' => '@saloonphp',
        ]), $fixtureData['data']);
    }

    public function testTheFixtureSwapToolWorksOnMultipleAttemptsAndRecursively()
    {
        $mockClient = new MockClient([
            new SuperheroFixture(),
            new SuperheroFixture(),
        ]);

        $responseA = connector()->send(new PagedSuperheroRequest(), $mockClient);
        $responseB = connector()->send(new PagedSuperheroRequest(), $mockClient);

        foreach ($responseA->json()['data'] as $item) {
            $this->assertArrayHasKey('publisher', $item);
            $this->assertEquals('DC Comics', $item['publisher']);
        }

        foreach ($responseB->json()['data'] as $item) {
            $this->assertArrayHasKey('publisher', $item);
            $this->assertEquals('REDACTED', $item['publisher']);
        }
    }

    public function testYouCanDefineACustomRedactionMethodForNonJsonBodyFixtures()
    {
        $mockClient = new MockClient([
            new BeforeSaveUserFixture(),
            new BeforeSaveUserFixture(),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);
        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals(200, $responseA->status());
        $this->assertEquals(222, $responseB->status());
    }

    public function testYouCanDefineRegexPatternsThatShouldBeUsedToReplaceTheBodyInFixtures()
    {
        $mockClient = new MockClient([
            new RegexUserFixture(),
            new RegexUserFixture(),
        ]);

        $responseA = connector()->send(new UserRequest(), $mockClient);
        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $responseA->json());

        $this->assertFalse($responseA->isFaked());
        $this->assertTrue($responseB->isFaked());

        $this->assertEquals([
            'name' => 'Sxxxmyjo20',
            'actual_name' => 'Sxxx',
            'twitter' => '**REDACTED-TWITTER**',
        ], $responseB->json());

        $fixtureData = json_decode(file_get_contents('tests/Fixtures/Saloon/Testing/user.json'), true);

        $this->assertEquals(json_encode([
            'name' => 'Sxxxmyjo20',
            'actual_name' => 'Sxxx',
            'twitter' => '**REDACTED-TWITTER**',
        ]), $fixtureData['data']);
    }

    public function testRequestAndResponseMiddlewareIsInvokedWhenUsingFakeResponses()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 200, ['X-Foo' => 'Bar']),
            MockResponse::make(['name' => 'Alex']),
            MockResponse::make(['error' => 'Server Unavailable'], 500),
        ]);

        $middlewareA = false;
        $middlewareB = false;
        $middlewareC = false;
        $middlewareD = false;

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $request = new UserRequest();

        $connector->middleware()->onRequest(function () use (&$middlewareA) {
            $middlewareA = true;
        });

        $connector->middleware()->onResponse(function () use (&$middlewareB) {
            $middlewareB = true;
        });

        $request->middleware()->onRequest(function () use (&$middlewareC) {
            $middlewareC = true;
        });

        $request->middleware()->onResponse(function () use (&$middlewareD) {
            $middlewareD = true;
        });

        $responseA = $connector->send($request);

        $this->assertTrue($middlewareA);
        $this->assertTrue($middlewareB);
        $this->assertTrue($middlewareC);
        $this->assertTrue($middlewareD);
    }

    public function testFixturesAreStillRecordedOnTheFirstRequest()
    {
        $mockClient = new MockClient([
            MockResponse::fixture('user'), // Test Exact Route
        ]);

        connector()->send(new UserRequest(), $mockClient);

        $mockClient->assertSent(UserRequest::class);
    }
}
