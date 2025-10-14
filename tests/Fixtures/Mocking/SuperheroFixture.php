<?php

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;

class SuperheroFixture extends Fixture
{
    /**
     * @return string
     */
    protected function defineName()
    {
        return 'superhero';
    }

    /**
     * @return string[]
     */
    protected function defineSensitiveJsonParameters()
    {
        return [
            'publisher' => 'REDACTED',
        ];
    }
}
