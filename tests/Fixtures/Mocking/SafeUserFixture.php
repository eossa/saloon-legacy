<?php

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class SafeUserFixture extends Fixture
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
     * Define the sensitive headers
     *
     * @return array
     */
    protected function defineSensitiveHeaders()
    {
        return [
            'Server' => 'secret',

            // Check for case-insensitive
            'cache-control' => function ($value) {
                return $value . ', yeehaw';
            },
        ];
    }

    /**
     * Swap any sensitive JSON parameters
     *
     * @return array
     */
    protected function defineSensitiveJsonParameters()
    {
        return [
            // You can also define callables that should be run to replace the value!

            'name' => static function ($value) {
                return substr_replace($value, 'xxx', 1);
            },
            'actual_name' => 'REDACTED',
            'twitter' => '@saloonphp',
        ];
    }
}
