<?php

namespace Saloon\Tests\Unit;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Helpers\Helpers;
use PHPUnit\Framework\TestCase;
use Saloon\Traits\Body\HasXmlBody;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Exceptions\BodyException;
use Saloon\Traits\Body\HasStringBody;
use Saloon\Traits\Body\ChecksForHasBody;
use Saloon\Traits\Body\HasMultipartBody;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class BodyTraitTest extends TestCase
{
    /**
     * @dataProvider bodyTraitProvider
     */
    public function testEachOfTheBodyTraitsHasTheChecksForWithBodyTraitAdded($trait)
    {
        $uses = Helpers::classUsesRecursive($trait);

        $this->assertArrayHasKey(ChecksForHasBody::class, $uses);
        $this->assertEquals(ChecksForHasBody::class, $uses[ChecksForHasBody::class]);
    }

    public function bodyTraitProvider()
    {
        return [
            [HasStringBody::class],
            [HasFormBody::class],
            [HasJsonBody::class],
            [HasMultipartBody::class],
            [HasXmlBody::class],
        ];
    }

    public function testWhenABodyTraitIsAddedToARequestWithoutWithBodyItWillThrowAnException()
    {
        $request = new TestRequestWithJsonBody();

        $this->expectException(BodyException::class);
        $this->expectExceptionMessage('You have added a body trait without implementing `Saloon\Contracts\Body\HasBody` on your request or connector.');

        TestConnector::make()->send($request);
    }

    public function testWhenABodyTraitIsAddedToAConnectorWithoutWithBodyItWillThrowAnException()
    {
        $connector = new TestConnectorWithJsonBody();

        $this->expectException(BodyException::class);
        $this->expectExceptionMessage('You have added a body trait without implementing `Saloon\Contracts\Body\HasBody` on your request or connector.');

        $connector->send(new UserRequest());
    }
}

class TestRequestWithJsonBody extends Request
{
    use HasJsonBody;

    protected $method = Method::GET;

    public function resolveEndpoint()
    {
        return '';
    }
}

class TestConnectorWithJsonBody extends Connector
{
    use HasJsonBody;

    public function resolveBaseUrl()
    {
        return '';
    }
}
