<?php

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class RegexUserFixture extends Fixture
{
    /**
     * Define the name of the fixture
     *
     * @return string
     */
    protected function defineName()
    {
        return 'user';
    }

    /**
     * Define regex patterns that should be replaced
     *
     * @return array|callable[]|string[]
     */
    protected function defineSensitiveRegexPatterns()
    {
        return [
            // Twitter Handle
            '/@[a-z0-9_]{0,100}/' => '**REDACTED-TWITTER**',
            // The name Sam
            '/Sam/' => function ($value) {
                return substr_replace($value, 'xxx', 1);
            },
        ];
    }
}
