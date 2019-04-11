<?php

namespace Kirby\Toolkit;

class ControllerTest extends TestCase
{
    public function testCall()
    {
        $controller = new Controller(function () {
            return 'test';
        });

        $this->assertEquals('test', $controller->call());
    }

    public function testArguments()
    {
        $controller = new Controller(function ($a, $b) {
            return $a . $b;
        });

        $this->assertEquals('AB', $controller->call(null, [
            'a' => 'A',
            'b' => 'B'
        ]));
    }

    public function testBind()
    {
        $model = new Obj(['foo' => 'bar']);

        $controller = new Controller(function () {
            return $this;
        });

        $this->assertEquals($model, $controller->call($model));
    }

    public function testMissingParameter()
    {
        $controller = new Controller(function ($a) {
            return $a;
        });

        $this->assertNull($controller->call());
    }

    public function testLoad()
    {
        $controller = Controller::load(__DIR__ . '/fixtures/controller/controller.php');
        $this->assertEquals('loaded', $controller->call());
    }

    public function testLoadNonExisting()
    {
        $controller = Controller::load(__DIR__ . '/fixtures/controller/does-not-exist.php');
        $this->assertEquals(null, $controller);
    }

    public function testLoadInvalidController()
    {
        $controller = Controller::load(__DIR__ . '/fixtures/controller/invalid.php');
        $this->assertNull($controller);
    }
}
