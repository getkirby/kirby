<?php

namespace Kirby\Cms;

class StoreTest extends TestCase
{

    public function testWithoutBinding()
    {
        $test  = $this;
        $store = new Store([
            'doSomething' => function () use ($test) {
                $test->assertInstanceOf(Store::class, $this);
            }
        ]);

        $store->commit('doSomething');
    }

    public function testWithBinding()
    {
        $test  = $this;
        $bind  = new Object([]);
        $store = new Store([
            'doSomething' => function () use ($test, $bind) {
                $test->assertInstanceOf(Object::class, $this);
                $test->assertEquals($bind, $this);
            }
        ], $bind);

        $store->commit('doSomething');
    }

    public function testCommitArguments()
    {
        $test  = $this;
        $store = new Store([
            'doSomething' => function ($a, $b) use ($test) {
                $test->assertEquals('a', $a);
                $test->assertEquals('b', $b);
            }
        ]);

        $store->commit('doSomething', 'a', 'b');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid store action: "doesNotExist"
     */
    public function testInvalidCommit()
    {
        $store = new Store();
        $store->commit('doesNotExist');
    }

}
