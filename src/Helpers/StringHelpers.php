<?php

namespace Saloon\Helpers;

use Exception;
use Traversable;

/**
 * @internal
 */
final class StringHelpers
{
    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string|iterable<string> $patterns
     * @param string $value
     *
     * @return bool
     */
    public static function matchesPattern($patterns, $value)
    {
        if (! (is_array($patterns) || $patterns instanceof Traversable)) {
            $patterns = [$patterns];
        }

        foreach ($patterns as $pattern) {
            $pattern = (string)$pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $prefix
     *
     * @return string
     */
    public static function start($value, $prefix)
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int<1, max> $length
     *
     * @return string
     *
     * @throws Exception
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = mb_strlen($string)) < $length) {
            /** @var int<1, max> $size */
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= mb_substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
