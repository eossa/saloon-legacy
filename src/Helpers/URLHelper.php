<?php

namespace Saloon\Helpers;

/**
 * @internal
 */
class URLHelper
{
    /**
     * Check if a URL matches a given pattern
     *
     * @param string $pattern
     * @param string $value
     *
     * @return bool
     */
    public static function matches($pattern, $value)
    {
        return StringHelpers::matchesPattern(StringHelpers::start($pattern, '*'), $value);
    }

    /**
     * Join a base url and an endpoint together.
     *
     * @param string $baseUrl
     * @param string $endpoint
     *
     * @return string
     */
    public static function join($baseUrl, $endpoint)
    {
        if (static::isValidUrl($endpoint)) {
            return $endpoint;
        }

        if ($endpoint !== '/') {
            $endpoint = ltrim($endpoint, '/ ');
        }

        $requiresTrailingSlash = ! empty($endpoint) && $endpoint !== '/';

        $baseEndpoint = rtrim($baseUrl, '/ ');

        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . '/' : $baseEndpoint;

        return $baseEndpoint . $endpoint;
    }

    /**
     * Check if the URL is a valid URL
     *
     * @param string $url
     *
     * @return bool
     */
    public static function isValidUrl($url)
    {
        return ! empty(filter_var($url, FILTER_VALIDATE_URL));
    }

    /**
     * Parse a query string into an array
     *
     * @param string $query
     *
     * @return array<string, mixed>
     */
    public static function parseQueryString($query)
    {
        if ($query === '') {
            return [];
        }

        $parameters = [];

        foreach (explode('&', $query) as $parameter) {
            $name = urldecode((string)strtok($parameter, '='));
            $value = urldecode((string)strtok('='));

            if (! $name || strpos($parameter, '=') === 0) {
                continue;
            }

            $parameters[$name] = $value;
        }

        return $parameters;
    }
}
