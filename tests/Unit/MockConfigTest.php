<?php

namespace Saloon\Tests\Unit;

use Saloon\MockConfig;
use League\Flysystem\Filesystem;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\FixtureMissingException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class MockConfigTest extends TestCase
{
    protected function tearDown()
    {
        MockConfig::setFixturePath('tests/Fixtures/Saloon');
    }

    public function testYouCanChangeTheDefaultFixturePath()
    {
        $this->assertEquals('tests/Fixtures/Saloon', MockConfig::getFixturePath());

        MockConfig::setFixturePath('saloon-requests/responses');

        $this->assertEquals('saloon-requests/responses', MockConfig::getFixturePath());
    }

    public function testYouCanThrowAnExceptionIfTheFixtureDoesNotExist()
    {
        MockConfig::setFixturePath('tests/Fixtures/Saloon');

        $this->assertFalse(MockConfig::isThrowingOnMissingFixtures());

        MockConfig::throwOnMissingFixtures();

        $this->expectException(FixtureMissingException::class);
        $this->expectExceptionMessage('The fixture "example.json" could not be found in storage.');

        $mockClient = new MockClient([
            MockResponse::fixture('example'),
        ]);

        connector()->send(new UserRequest(), $mockClient);
    }

    public function testIfTheDefaultFixturePathDoesntExistItWillBeCreated()
    {
        $filesystem = new Filesystem(new Local('tests/Fixtures'));
        $filesystem->deleteDir('OtherFixturePath');

        MockConfig::setFixturePath('tests/Fixtures/OtherFixturePath');

        $this->assertFalse($filesystem->has('OtherFixturePath'));

        new MockClient([
            MockResponse::fixture('example'),
        ]);

        $this->assertTrue($filesystem->has('OtherFixturePath'));
    }
}
