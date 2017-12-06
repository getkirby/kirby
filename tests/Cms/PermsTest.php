<?php

namespace Kirby\Cms;

class PermsTest extends TestCase
{

    public function testWithoutBinding()
    {
        $test  = $this;
        $perms = new Perms([
            'doSomething' => function () use ($test) {
                $test->assertInstanceOf(Perms::class, $this);
            }
        ]);

        $perms->check('doSomething');
    }

    public function testWithBinding()
    {
        $test  = $this;
        $bind  = new Object([]);
        $perms = new Perms([
            'doSomething' => function () use ($test, $bind) {
                $test->assertInstanceOf(Object::class, $this);
                $test->assertEquals($bind, $this);
            }
        ], $bind);

        $perms->check('doSomething');
    }

    public function testCheckResult()
    {
        $perms = new Perms([
            'isA' => function ($input) {
                return $input === 'a';
            }
        ]);

        $this->assertTrue($perms->check('isA', 'a'));
        $this->assertFalse($perms->check('isA', 'b'));
    }

    public function testCheckArguments()
    {
        $test  = $this;
        $perms = new Perms([
            'doSomething' => function ($a, $b) use ($test) {
                $test->assertEquals('a', $a);
                $test->assertEquals('b', $b);
            }
        ]);

        $perms->check('doSomething', 'a', 'b');
    }

    public function testMissingCheck()
    {
        $perms = new Perms();
        $this->assertTrue($perms->check('doesNotExist'));
    }

}
