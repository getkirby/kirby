<?php

namespace Kirby\Toolkit\Traits;

use PHPUnit\Framework\TestCase;

class CustomMethodsTest extends TestCase
{

    protected function _user()
    {
        CustomMethodsTraitsUser::addCustomMethod('hello', function () {
            return 'hello';
        });

        CustomMethodsTraitsUser::addCustomMethod('username', function ($username) {
            return $username;
        });

        return new CustomMethodsTraitsUser();
    }

    public function testHasCustomMethod()
    {
        $user = $this->_user();

        $this->assertTrue($user->hasCustomMethod('hello'));
        $this->assertFalse($user->hasCustomMethod('foo'));
    }

    public function testCallCustomMethod()
    {
        $user = $this->_user();
        $this->assertEquals('hello', $user->callCustomMethod('hello'));
    }

    public function testCallCustomMethodWithArgs()
    {
        $user = $this->_user();
        $this->assertEquals('test', $user->callCustomMethod('username', ['test']));
    }

    public function testMagicCall()
    {
        $user = $this->_user();
        $this->assertEquals('hello', $user->hello());
    }

    public function testMagicCallWithArgs()
    {
        $user = $this->_user();
        $this->assertEquals('test', $user->username('test'));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid custom method: hello
     */
    public function testRemoveCustomMethod()
    {
        $user = $this->_user();
        CustomMethodsTraitsUser::removeCustomMethods();

        $this->assertFalse($user->hasCustomMethod('hello'));
        $user->callCustomMethod('hello');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid custom method: username
     */
    public function testRemoveCustomMethodWithArgs()
    {
        $user = $this->_user();
        CustomMethodsTraitsUser::removeCustomMethods();

        $this->assertFalse($user->hasCustomMethod('username'));
        $user->callCustomMethod('username', ['test']);
    }
}
