<?php

namespace Saloon\Tests\Unit;

use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorBlankRequest;
use Saloon\Tests\Fixtures\Requests\OverwrittenQueryParameterConnectorRequest;
use PHPUnit\Framework\TestCase;

class QueryParameterTest extends TestCase
{
    public function testARequestWithQueryParamsAddedSendsTheQueryParams()
    {
        $request = new QueryParameterRequest();

        $request->query()->add('sort', '-created_at');

        $pendingRequest = connector()->createPendingRequest($request);

        $query = $pendingRequest->query()->all();

        $this->assertArrayHasKey('per_page', $query);
        $this->assertEquals(100, $query['per_page']); // Test Default
        $this->assertArrayHasKey('sort', $query);
        $this->assertEquals('-created_at', $query['sort']); // Test Adding Data After
    }

    public function testIfSetQueryIsUsedAllOtherDefaultQueryParamsWontBeIncluded()
    {
        $request = new QueryParameterConnectorRequest();

        $request->query()->set([
            'sort' => 'nickname',
        ]);

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertEquals(['sort' => 'nickname'], $query);
    }

    public function testAConnectorCanHaveQueryThatIsSet()
    {
        $request = new QueryParameterConnectorRequest();

        $pendingRequest = (new QueryParameterConnector())->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertArrayHasKey('sort', $query);
        $this->assertEquals('first_name', $query['sort']); // Added by connector
        $this->assertArrayHasKey('include', $query);
        $this->assertEquals('user', $query['include']); // Added by request
    }

    public function testARequestQueryParameterCanOverwriteAConnectorsParameter()
    {
        $request = new OverwrittenQueryParameterConnectorRequest();

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertEquals(['sort' => 'date_of_birth'], $query);
    }

    public function testManuallyOverwritingQueryParameterInRuntimeCanOverwriteConnectorParameter()
    {
        $request = new QueryParameterConnectorBlankRequest();

        $request->query()->add('sort', 'custom_field');

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertEquals(['sort' => 'custom_field'], $query);
    }

    public function testManuallyOverwritingQueryParameterInRuntimeCanOverwriteRequestParameter()
    {
        $request = new QueryParameterRequest();

        $request->query()->add('per_page', 500);

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertEquals(['per_page' => 500], $query);
    }

    public function testWhenNotSendingQueryParametersTheQueryOptionIsNotSet()
    {
        $request = new UserRequest();

        $pendingRequest = connector()->createPendingRequest($request);
        $query = $pendingRequest->query()->all();

        $this->assertEmpty($query);
    }
}
