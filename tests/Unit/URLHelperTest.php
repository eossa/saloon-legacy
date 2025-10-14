<?php

namespace Saloon\Tests\Unit;

use Saloon\Helpers\URLHelper;
use PHPUnit\Framework\TestCase;

class URLHelperTest extends TestCase
{
    /**
     * @dataProvider urlJoinProvider
     */
    public function testTheUrlHelperWillJoinTwoUrlsTogether($baseUrl, $endpoint, $expected)
    {
        $this->assertEquals($expected, URLHelper::join($baseUrl, $endpoint));
    }

    public function urlJoinProvider()
    {
        return [
            'base_with_slash_endpoint_with_slash' => ['https://google.com', '/search', 'https://google.com/search'],
            'base_with_slash_endpoint_without_slash' => ['https://google.com', 'search', 'https://google.com/search'],
            'base_with_trailing_slash_endpoint_with_slash' => ['https://google.com/', '/search', 'https://google.com/search'],
            'base_with_trailing_slash_endpoint_without_slash' => ['https://google.com/', 'search', 'https://google.com/search'],
            'base_with_double_slash_endpoint_with_double_slash' => ['https://google.com//', '//search', 'https://google.com/search'],
            'empty_base_full_endpoint' => ['', 'https://google.com/search', 'https://google.com/search'],
            'empty_base_relative_endpoint' => ['', 'google.com/search', '/google.com/search'],
            'base_url_overridden_by_full_endpoint' => ['https://google.com', 'https://api.google.com/search', 'https://api.google.com/search'],
        ];
    }

    /**
     * @dataProvider queryParameterProvider
     */
    public function testTheUrlHelperCanParseAVarietyOfQueryParameters($query, $expected)
    {
        $this->assertEquals($expected, URLHelper::parseQueryString($query));
    }

    public function queryParameterProvider()
    {
        return [
            'simple_parameter' => ['foo=bar', ['foo' => 'bar']],
            'multiple_parameters' => ['foo=bar&name=sam', ['foo' => 'bar', 'name' => 'sam']],
            'double_equals' => ['foo==bar&name=sam', ['foo' => 'bar', 'name' => 'sam']],
            'empty_key' => ['=abc&name=sam', ['name' => 'sam']],
            'empty_value' => ['foo&name=sam', ['foo' => '', 'name' => 'sam']],
            'dot_in_key' => ['account.id=1', ['account.id' => '1']],
            'url_encoded_value' => ['name=cowboy%20sam', ['name' => 'cowboy sam']],
            'trailing_ampersand' => ['name=sam&', ['name' => 'sam']],
        ];
    }
}
