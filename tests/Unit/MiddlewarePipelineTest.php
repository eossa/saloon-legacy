<?php

namespace Saloon\Tests\Unit;

use Saloon\Data\Pipe;
use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Helpers\Pipeline;
use PHPUnit\Framework\TestCase;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;

class MiddlewarePipelineTest extends TestCase
{
    public function testYouCanAddAPipeToTheRequestMiddleware()
    {
        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            })
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-Two', 'Howdy');
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest());
        $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

        $this->assertEquals('Yee-Haw', $pendingRequest->headers()->get('X-Pipe-One'));
        $this->assertEquals('Howdy', $pendingRequest->headers()->get('X-Pipe-Two'));
    }

    public function testYouCanAddANamedPipeToTheRequestMiddleware()
    {
        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            }, 'YeeHawPipe');

        $pipe = $pipeline->getRequestPipeline()->getPipes()[0];

        $this->assertInstanceOf(Pipe::class, $pipe);
        $this->assertEquals('YeeHawPipe', $pipe->name);
        $this->assertNull($pipe->order);

        $pendingRequest = connector()->createPendingRequest(new UserRequest());
        $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

        $this->assertEquals('Yee-Haw', $pendingRequest->headers()->get('X-Pipe-One'));
    }

    public function testTheNamedRequestPipeMustBeUnique()
    {
        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            }, 'YeeHawPipe');

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "YeeHawPipe" pipe already exists on the pipeline');

        $pipeline
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            }, 'YeeHawPipe');
    }

    public function testIfAPipeReturnsAPendingRequestWeWillUseThatInTheNextStep()
    {
        $pipeline = new MiddlewarePipeline();

        $errorRequest = connector()->createPendingRequest(new ErrorRequest());

        $pipeline
            ->onRequest(function (PendingRequest $request) use ($errorRequest) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');

                return $errorRequest;
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest());
        $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

        $this->assertSame($errorRequest, $pendingRequest);
    }

    public function testARequestPipelineIsRunInOrderOfPipes()
    {
        $pipeline = new MiddlewarePipeline();
        $names = [];

        $pipeline
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Sam';
            })
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Taylor';
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest());

        $pipeline->executeRequestPipeline($pendingRequest);

        $this->assertEquals(['Sam', 'Taylor'], $names);
    }

    public function testARequestPipeCanBeAddedToTheTopOfThePipeline()
    {
        $pipeline = new MiddlewarePipeline();
        $names = [];

        $pipeline
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Sam';
            })
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Taylor';
            }, null, PipeOrder::FIRST)
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Andrew';
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest());

        $pipeline->executeRequestPipeline($pendingRequest);

        $this->assertEquals(['Taylor', 'Sam', 'Andrew'], $names);
    }

    public function testARequestPipeCanBeAddedToTheBottomOfThePipeline()
    {
        $pipeline = new MiddlewarePipeline();
        $names = [];

        $pipeline
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Sam';
            })
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Taylor';
            }, null, PipeOrder::LAST)
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Andrew';
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest());

        $pipeline->executeRequestPipeline($pendingRequest);

        $this->assertEquals(['Sam', 'Andrew', 'Taylor'], $names);
    }

    public function testYouCanAddANamedPipeToTheResponseMiddleware()
    {
        $pipeline = new MiddlewarePipeline();

        $count = 0;

        $pipeline
            ->onResponse(function (Response $response) use (&$count) {
                $count++;
            }, 'ResponsePipe');

        $pipe = $pipeline->getResponsePipeline()->getPipes()[0];

        $this->assertInstanceOf(Pipe::class, $pipe);
        $this->assertEquals('ResponsePipe', $pipe->name);
        $this->assertNull($pipe->order);

        $factory = new HttpFactory();

        $pendingRequest = connector()->createPendingRequest(new UserRequest());
        $response = Response::fromPsrResponse(MockResponse::make()->createPsrResponse($factory, $factory), $pendingRequest, $pendingRequest->createPsrRequest());

        $pipeline->executeResponsePipeline($response);

        $this->assertEquals(1, $count);
    }

    public function testTheNamedResponsePipeMustBeUnique()
    {
        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onResponse(function (Response $response) {
                //
            }, 'ResponsePipe');

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "ResponsePipe" pipe already exists on the pipeline');

        $pipeline
            ->onResponse(function (Response $response) {
                //
            }, 'ResponsePipe');
    }

    public function testYouCanAddAPipeToTheResponseMiddleware()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $pipeline = new MiddlewarePipeline();

        $count = 0;
        $testCase = $this;

        $pipeline
            ->onResponse(function (Response $response) use (&$count, $testCase) {
                $testCase->assertInstanceOf(Response::class, $response);

                $count++;
            })
            ->onResponse(function (Response $response) use (&$count, $testCase) {
                $testCase->assertInstanceOf(Response::class, $response);

                $count++;
            });

        $response = connector()->send(new UserRequest(), $mockClient);
        $response = $pipeline->executeResponsePipeline($response);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(2, $count);
    }

    public function testIfAResponsePipeReturnsAResponseWeWillUseThatInTheNextStep()
    {
        $mockClient = new MockClient([
            'Saloon\Tests\Fixtures\Requests\ErrorRequest' => MockResponse::make(['error' => 'Server Error'], 500),
            'Saloon\Tests\Fixtures\Requests\UserRequest' => MockResponse::make(['name' => 'Sam']),
        ]);

        $pipeline = new MiddlewarePipeline();

        $errorResponse = connector()->send(new ErrorRequest(), $mockClient);

        $pipeline
            ->onResponse(function (Response $response) use ($errorResponse) {
                return $errorResponse;
            });

        $response = connector()->send(new UserRequest(), $mockClient);
        $response = $pipeline->executeResponsePipeline($response);

        $this->assertSame($errorResponse, $response);
    }

    public function testAResponsePipeIsRunInOrderOfThePipes()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $names = [];

        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Sam';
            })
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Taylor';
            });

        $response = connector()->send(new UserRequest(), $mockClient);

        $pipeline->executeResponsePipeline($response);

        $this->assertEquals(['Sam', 'Taylor'], $names);
    }

    public function testAResponsePipeCanBeAddedToTheTopOfThePipeline()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $names = [];

        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Sam';
            })
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Taylor';
            }, null, PipeOrder::FIRST)
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Andrew';
            });

        $response = connector()->send(new UserRequest(), $mockClient);

        $pipeline->executeResponsePipeline($response);

        $this->assertEquals(['Taylor', 'Sam', 'Andrew'], $names);
    }

    public function testAResponsePipeCanBeAddedToTheBottomOfThePipeline()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $names = [];

        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Sam';
            })
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Taylor';
            }, null, PipeOrder::LAST)
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Andrew';
            });

        $response = connector()->send(new UserRequest(), $mockClient);

        $pipeline->executeResponsePipeline($response);

        $this->assertEquals(['Sam', 'Andrew', 'Taylor'], $names);
    }

    public function testYouCanAddAPipeToTheFatalMiddleware()
    {
        $pipeline = new MiddlewarePipeline();

        $count = 0;
        $testCase = $this;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$count, $testCase) {
                $testCase->assertInstanceOf(FatalRequestException::class, $exception);

                $count++;
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$count, $testCase) {
                $testCase->assertInstanceOf(FatalRequestException::class, $exception);
                $count++;
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            $this->assertInstanceOf(FatalRequestException::class, $e);
            $this->assertEquals(2, $count);
        }
    }

    public function testYouCanAddANamedPipeToTheFatalMiddleware()
    {
        $pipeline = new MiddlewarePipeline();

        $count = 0;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$count) {
                $count++;
            }, 'FatalPipe');

        $pipe = $pipeline->getFatalPipeline()->getPipes()[0];

        $this->assertInstanceOf(Pipe::class, $pipe);
        $this->assertEquals('FatalPipe', $pipe->name);
        $this->assertNull($pipe->order);

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            $this->assertInstanceOf(FatalRequestException::class, $e);
            $this->assertEquals(1, $count);
        }
    }

    public function testTheNamedFatalPipeMustBeUnique()
    {
        $pipeline = new MiddlewarePipeline();

        $count = 0;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$count) {
                $count++;
            }, 'YeeHawPipe');

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "YeeHawPipe" pipe already exists on the pipeline');

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$count) {
                $count++;
            }, 'YeeHawPipe');
    }

    public function testAFatalPipeIsRunInOrderOfThePipes()
    {
        $names = [];

        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Sam';
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Taylor';
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            $this->assertEquals(['Sam', 'Taylor'], $names);
        }
    }

    public function testAFatalPipeCanBeAddedToTheTopOfThePipeline()
    {
        $names = [];

        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Sam';
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Taylor';
            }, null, PipeOrder::FIRST)
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Andrew';
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            $this->assertEquals(['Taylor', 'Sam', 'Andrew'], $names);
        }
    }

    public function testAFatalPipeCanBeAddedToTheBottomOfThePipeline()
    {
        $names = [];

        $pipeline = new MiddlewarePipeline();

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Sam';
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Taylor';
            }, null, PipeOrder::LAST)
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Andrew';
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            $this->assertEquals(['Sam', 'Andrew', 'Taylor'], $names);
        }
    }

    public function testYouCanMergeAMiddlewarePipelineTogether()
    {
        $pipelineA = new MiddlewarePipeline();
        $pipelineB = new MiddlewarePipeline();

        $pipelineA
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            })
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Howdy');
            })
            ->onResponse(function (Response $response) {
                return $response->throw();
            }, 'response');

        $this->assertEmpty($pipelineB->getRequestPipeline()->getPipes());
        $this->assertEmpty($pipelineB->getResponsePipeline()->getPipes());

        $pipelineB->merge($pipelineA);

        $this->assertCount(2, $pipelineB->getRequestPipeline()->getPipes());
        $this->assertCount(1, $pipelineB->getResponsePipeline()->getPipes());
        $this->assertEquals($pipelineA->getRequestPipeline()->getPipes(), $pipelineB->getRequestPipeline()->getPipes());
        $this->assertEquals($pipelineA->getResponsePipeline()->getPipes(), $pipelineB->getResponsePipeline()->getPipes());
    }

    public function testWhenMergingAMiddlewarePipelineTogetherIfTwoPipelinesExistWithTheSamePipeItThrowsAnException()
    {
        $pipelineA = new MiddlewarePipeline();
        $pipelineB = new MiddlewarePipeline();

        $pipelineA->onRequest(function () {
            return null;
        }, 'howdy');
        $pipelineB->onRequest(function () {
            return null;
        }, 'howdy');

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "howdy" pipe already exists on the pipeline');

        $pipelineA->merge($pipelineB);
    }
}
