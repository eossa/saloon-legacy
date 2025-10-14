<?php

namespace Saloon\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Saloon\Tests\Fixtures\Requests\UserRequest;

class ConditionableTest extends TestCase
{
    public function testYouCanUseTheWhenMethodToInvokeACallbackWhenAGivenConditionIsTruthy()
    {
        $request = new UserRequest();

        $request->when(true, function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Sam');
        });

        $request->when(false, function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Alex');
        });

        $this->assertArrayHasKey('X-Name', $request->headers()->all());
        $this->assertEquals('Sam', $request->headers()->all()['X-Name']);
        $this->assertArrayNotHasKey('X-Name-Alex', $request->headers()->all());
    }

    public function testYouCanUseTheUnlessMethodToInvokeACallbackWhenAGivenConditionIsFalsy()
    {
        $request = new UserRequest();

        $request->unless(true, function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Sam');
        });

        $request->unless(false, function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Alex');
        });

        $this->assertArrayHasKey('X-Name', $request->headers()->all());
        $this->assertEquals('Alex', $request->headers()->all()['X-Name']);
    }

    public function testYouCanProvideACallbackAsTheValueOfTheWhenCondition()
    {
        $request = new UserRequest();

        $request->when(
            function () { return true; },
            function (UserRequest $request) {
                $request->headers()->add('X-Name', 'Sam');
            }
        );

        $this->assertArrayHasKey('X-Name', $request->headers()->all());
        $this->assertEquals('Sam', $request->headers()->all()['X-Name']);
    }

    public function testYouCanProvideACallbackAsTheValueOfTheUnlessCondition()
    {
        $request = new UserRequest();

        $request->unless(
            function () { return false; },
            function (UserRequest $request) {
                $request->headers()->add('X-Name', 'Alex');
            }
        );

        $this->assertArrayHasKey('X-Name', $request->headers()->all());
        $this->assertEquals('Alex', $request->headers()->all()['X-Name']);
    }

    public function testYouCanProvideACallbackAsTheDefaultValueOfTheWhenCondition()
    {
        $request = new UserRequest();

        $request->when(
            false,
            function (UserRequest $request) {
                $request->headers()->add('X-Name', 'Sam');
            },
            function (UserRequest $request) {
                $request->headers()->add('X-Name', 'Alex');
            }
        );

        $this->assertArrayHasKey('X-Name', $request->headers()->all());
        $this->assertEquals('Alex', $request->headers()->all()['X-Name']);
    }

    public function testYouCanProvideACallbackAsTheDefaultValueOfTheUnlessCondition()
    {
        $request = new UserRequest();

        $request->unless(
            true,
            function (UserRequest $request) {
                $request->headers()->add('X-Name', 'Sam');
            },
            function (UserRequest $request) {
                $request->headers()->add('X-Name', 'Alex');
            }
        );

        $this->assertArrayHasKey('X-Name', $request->headers()->all());
        $this->assertEquals('Alex', $request->headers()->all()['X-Name']);
    }

    public function testItWillPassTheConditionValueAsTheSecondArgumentOfTheCallable()
    {
        $request = new UserRequest();

        $testCase = $this;
        $request->when(true, function (UserRequest $request, $value) use ($testCase) {
            $testCase->assertInternalType('bool', $value);
            $testCase->assertTrue($value);
        });

        $request->unless(false, function (UserRequest $request, $value) use ($testCase) {
            $testCase->assertInternalType('bool', $value);
            $testCase->assertFalse($value);
        });
    }

    public function testItWillPassTheConditionValueAsTheSecondArgumentOfTheDefaultCallable()
    {
        $request = new UserRequest();

        $testCase = $this;
        $request->when(
            false,
            function (UserRequest $request, $value) {
                // Do nothing
            },
            function (UserRequest $request, $value) use ($testCase) {
                $testCase->assertInternalType('bool', $value);
                $testCase->assertFalse($value);
            }
        );

        $request->unless(
            true,
            function (UserRequest $request, $value) {
                // Do nothing
            },
            function (UserRequest $request, $value) use ($testCase) {
                $testCase->assertInternalType('bool', $value);
                $testCase->assertTrue($value);
            }
        );
    }
}
