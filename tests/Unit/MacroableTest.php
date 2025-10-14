<?php

namespace Saloon\Tests\Unit;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Saloon\Traits\Macroable;

class MacroableTest extends TestCase
{
    protected $macroableClass;

    protected function setUp()
    {
        $this->macroableClass = new MacroableTestClass();
    }

    public function testANewMacroCanBeRegisteredAndCalled()
    {
        call_user_func([$this->macroableClass, 'macro'], 'newMethod', function () {
            return 'newValue';
        });

        $this->assertEquals('newValue', $this->macroableClass->newMethod());
    }

    public function testANewMacroCanBeRegisteredAndCalledStatically()
    {
        call_user_func([$this->macroableClass, 'macro'], 'newMethod', function () {
            return 'newValue';
        });

        $this->assertEquals('newValue', call_user_func([get_class($this->macroableClass), 'newMethod']));
    }

    public function testAClassCanBeRegisteredAsANewMacroAndBeInvoked()
    {
        call_user_func([$this->macroableClass, 'macro'], 'newMethod', new MacroableInvokableClass());

        $this->assertEquals('newValue', $this->macroableClass->newMethod());
        $this->assertEquals('newValue', call_user_func([get_class($this->macroableClass), 'newMethod']));
    }

    public function testItPassesParametersCorrectly()
    {
        call_user_func([$this->macroableClass, 'macro'], 'concatenate', function () {
            $strings = func_get_args();
            return implode('-', $strings);
        });

        $this->assertEquals('one-two-three', $this->macroableClass->concatenate('one', 'two', 'three'));
    }

    public function testRegisteredMethodsAreBoundToTheClass()
    {
        call_user_func([$this->macroableClass, 'macro'], 'newMethod', function () {
            return $this->privateVariable;
        });

        $this->assertEquals('privateValue', $this->macroableClass->newMethod());
    }

    public function testItCanWorkOnStaticMethods()
    {
        call_user_func([$this->macroableClass, 'macro'], 'testStatic', function () {
            return $this::getPrivateStatic();
        });

        $this->assertEquals('privateStaticValue', $this->macroableClass->testStatic());
    }

    public function testItCanMixinAllPublicMethodsFromAnotherClass()
    {
        $mixinClass = new MacroableMixinClass();

        call_user_func([$this->macroableClass, 'mixin'], $mixinClass);

        $this->assertEquals('privateValue-test', $this->macroableClass->mixinMethodA('test'));
    }

    public function testItWillThrowAnExceptionIfAMethodDoesNotExist()
    {
        $this->expectException(BadMethodCallException::class);

        $this->macroableClass->nonExistingMethod();
    }

    public function testItWillThrowAnExceptionIfAStaticMethodDoesNotExist()
    {
        $this->expectException(BadMethodCallException::class);

        call_user_func([get_class($this->macroableClass), 'nonExistingMethod']);
    }
}

class MacroableTestClass
{
    private $privateVariable = 'privateValue';

    use Macroable;

    private static function getPrivateStatic()
    {
        return 'privateStaticValue';
    }
}

class MacroableInvokableClass
{
    public function __invoke()
    {
        return 'newValue';
    }
}

class MacroableMixinClass
{
    private function secretMixinMethod()
    {
        return 'secret';
    }

    public function mixinMethodA()
    {
        return function ($value) {
            return $this->mixinMethodB($value);
        };
    }

    public function mixinMethodB()
    {
        return function ($value) {
            return $this->privateVariable . '-' . $value;
        };
    }
}
