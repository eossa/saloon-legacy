<?php

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\Fixture;
use Saloon\Data\RecordedResponse;

class BeforeSaveUserFixture extends Fixture
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
     * Modify the fixture before it is sent
     *
     * @return RecordedResponse
     */
    protected function beforeSave(RecordedResponse $recordedResponse)
    {
        $recordedResponse->statusCode = 222;

        return $recordedResponse;
    }
}
