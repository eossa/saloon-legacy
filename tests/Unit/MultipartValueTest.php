<?php

namespace Saloon\Tests\Unit;

use GuzzleHttp\Psr7\Utils;
use Saloon\Data\MultipartValue;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class MultipartValueTest extends TestCase
{
    /**
     * @dataProvider validValuesProvider
     */
    public function testItCanAcceptDifferentValues($value)
    {
        $multipartValue = new MultipartValue('test', $value);
        $this->assertEquals($value, $multipartValue->value);
    }

    public function validValuesProvider()
    {
        return [
            'stream' => [Utils::streamFor('hello')],
            'resource' => [fopen(sprintf('data://text/plain,%s', 'hello'), 'rb')],
            'string' => ['hello'],
            'integer' => [123],
            'float' => [123.50],
        ];
    }

    /**
     * @dataProvider invalidValuesProvider
     */
    public function testItWillThrowAnExceptionOnInvalidValue($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value property must be either a Psr\Http\Message\StreamInterface, resource, string or numeric.');

        new MultipartValue('test', $value);
    }

    public function invalidValuesProvider()
    {
        return [
            'array' => [[]],
            'object' => [new UserRequest()],
        ];
    }
}
