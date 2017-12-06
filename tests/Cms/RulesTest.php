<?php

namespace Kirby\Cms;

class RulesTest extends TestCase
{

    public function testWithoutBinding()
    {
        $test  = $this;
        $rules = new Rules([
            'doSomething' => function () use ($test) {
                $test->assertInstanceOf(Rules::class, $this);
            }
        ]);

        $rules->check('doSomething');
    }

    public function testWithBinding()
    {
        $test  = $this;
        $bind  = new Object([]);
        $rules = new Rules([
            'doSomething' => function () use ($test, $bind) {
                $test->assertInstanceOf(Object::class, $this);
                $test->assertEquals($bind, $this);
            }
        ], $bind);

        $rules->check('doSomething');
    }

    public function testCheckResult()
    {
        $rules = new Rules([
            'isA' => function ($input) {
                return $input === 'a';
            }
        ]);

        $this->assertTrue($rules->check('isA', 'a'));
        $this->assertFalse($rules->check('isA', 'b'));
    }

    public function testCheckArguments()
    {
        $test  = $this;
        $rules = new Rules([
            'doSomething' => function ($a, $b) use ($test) {
                $test->assertEquals('a', $a);
                $test->assertEquals('b', $b);
            }
        ]);

        $rules->check('doSomething', 'a', 'b');
    }

    public function testMissingCheck()
    {
        $rules = new Rules();
        $this->assertTrue($rules->check('doesNotExist'));
    }

}
