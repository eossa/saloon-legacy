<?php

namespace Saloon\Tests\Unit;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use PHPUnit\Framework\TestCase;

class PsrTest extends TestCase
{
    public function testAPsr7RequestCanBeCreatedFromThePendingRequest()
    {
        $connector = new TestConnector();
        $request = new UserRequest();

        $pendingRequest = $connector->createPendingRequest($request);
        $request = $pendingRequest->createPsrRequest();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertEquals('https://tests.saloon.dev/api/user', (string)$request->getUri());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals([
            'Host' => ['tests.saloon.dev'],
            'Accept' => ['application/json'],
        ], $request->getHeaders());

        $this->assertEquals('1.1', $request->getProtocolVersion());
    }

    public function testIfRequestBodyIsPresentThenItWillBeOnThePsr7Request()
    {
        $connector = new TestConnector();
        $request = new HasJsonBodyRequest();

        $pendingRequest = $connector->createPendingRequest($request);
        $request = $pendingRequest->createPsrRequest();

        $body = $request->getBody();

        $this->assertInstanceOf(StreamInterface::class, $body);
        $this->assertEquals('{"name":"Sam","catchphrase":"Yeehaw!"}', $body->getContents());
    }

    public function testYouCanGenerateAUriFromThePendingRequest()
    {
        $connector = new QueryParameterConnector();
        $request = new QueryParameterRequest('/user?include=hats#fragment-123');

        $pendingRequest = $connector->createPendingRequest($request);
        $uri = $pendingRequest->getUri();

        $this->assertInstanceOf(UriInterface::class, $uri);

        $this->assertEquals('https://tests.saloon.dev/api/user?include=hats&sort=first_name&per_page=100#fragment-123', (string)$uri);
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('tests.saloon.dev', $uri->getHost());
        $this->assertEquals('/api/user', $uri->getPath());
        $this->assertEquals('include=hats&sort=first_name&per_page=100', $uri->getQuery());
        $this->assertEquals('fragment-123', $uri->getFragment());
    }

    public function testWhenUsingTheUrlForQueryParametersYouCanUseDotsAndValueLessParameters()
    {
        $connector = new TestConnector();
        $request = new QueryParameterRequest('/user?account.id=1&checked&name=sam');

        $pendingRequest = $connector->createPendingRequest($request);
        $uri = $pendingRequest->getUri();

        $this->assertEquals('account.id=1&checked=&name=sam&per_page=100', $uri->getQuery());
    }
}
