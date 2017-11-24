<?php

namespace Kirby\Toolkit\DI;

use stdClass;
use Exception;
use PHPUnit\Framework\TestCase;

class Foo { public function test() {} }
class Bar {}

class TestCaller
{

    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function simple(Foo $foo, Bar $bar)
    {
        $this->test->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
        $this->test->assertInstanceOf('Kirby\Toolkit\DI\Bar', $bar);
    }

    public function withArguments($a, Foo $foo, Bar $bar, $b)
    {
        $this->test->assertEquals('a', $a);
        $this->test->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
        $this->test->assertInstanceOf('Kirby\Toolkit\DI\Bar', $bar);
        $this->test->assertEquals('b', $b);
    }

    public function withNewThis($a, Foo $foo, Bar $bar, $b)
    {
        // this should now be $testcase instance
        $this->assertEquals('a', $a);
        $this->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
        $this->assertInstanceOf('Kirby\Toolkit\DI\Bar', $bar);
        $this->assertEquals('b', $b);
    }
}

class DependenciesTest extends TestCase
{

    public function testString()
    {
        $dependencies = new Dependencies;
        $result       = $dependencies->set('foo', 'bar');

        $this->assertEquals($dependencies, $result);
        $this->assertEquals('bar', $dependencies->get('foo'));
    }

    public function testClassName()
    {
        $dependencies = new Dependencies;
        $result       = $dependencies->set('foo', '\stdClass');

        $this->assertEquals($dependencies, $result);
        $this->assertInstanceOf('stdClass', $dependencies->get('foo'));
    }

    public function testInstance()
    {
        $dependencies = new Dependencies;
        $instance     = new \stdClass;
        $result       = $dependencies->set('foo', $instance);

        $this->assertEquals($dependencies, $result);
        $this->assertEquals($instance, $dependencies->get('foo'));
    }

    public function testClosure()
    {
        $dependencies = new Dependencies;
        $result       = $dependencies->set('foo', function () {
            return 'bar';
        });

        $this->assertEquals($dependencies, $result);
        $this->assertEquals('bar', $dependencies->get('foo'));
    }

    /**
     * @expectedException Exception
     * $expectedExceptionMessage "foo" can no longer be overwritten
     */
    public function testSetterAfterGet()
    {
        $dependencies = new Dependencies;
        $dependencies->set('foo', 'bar');

        $this->assertEquals('bar', $dependencies->get('foo'));

        $dependencies->set('foo', 'foo');
    }

    public function testGetterWithArguments()
    {

        // instance
        $dependencies = new Dependencies;
        $dependencies->set('foo', 'Exception');
        $object = $dependencies->get('foo', 'test', 1);

        $this->assertEquals('test', $object->getMessage());
        $this->assertEquals(1, $object->getCode());

        // closure
        $dependencies = new Dependencies;
        $dependencies->set('foo', function ($message, $code) {
            return new Exception($message, $code);
        });

        $object = $dependencies->get('foo', 'test', 1);

        $this->assertEquals('test', $object->getMessage());
        $this->assertEquals(1, $object->getCode());
    }

    /**
     * @expectedException  Exception
     * @expectedExceptionMessage The dependency does not exist: does-not-exist
     */
    public function testGetterWithMissingDependency()
    {
        $dependencies = new Dependencies;
        $dependencies->set('foo', 'bar');

        $dependencies->get('does-not-exist');
    }

    public function testSingleton()
    {
        // instances
        $dependencies = new Dependencies;
        $dependencies->set('test', 'stdClass');

        $a = $dependencies->get('test');
        $b = $dependencies->get('test');

        $this->assertFalse($a === $b);

        // singleton via set
        $dependencies = new Dependencies;
        $dependencies->set('test', 'stdClass', ['singleton' => true]);

        $a = $dependencies->get('test');
        $b = $dependencies->get('test');

        $this->assertTrue($a === $b);

        // singleton via singleton method
        $dependencies = new Dependencies;
        $dependencies->singleton('test', 'stdClass');

        $a = $dependencies->get('test');
        $b = $dependencies->get('test');

        $this->assertTrue($a === $b);
    }

    public function testHas()
    {
        $dependencies = new Dependencies;

        $this->assertFalse($dependencies->has('foo'));

        $dependencies->set('foo', 'bar');

        $this->assertTrue($dependencies->has('foo'));
    }

    public function testInitialize()
    {
        $dependencies = new Dependencies;

        $expected = 'string';
        $result   = $dependencies->initialize($expected);
        $this->assertEquals($expected, $result);

        $expected = new stdClass;
        $result   = $dependencies->initialize('stdClass');
        $this->assertEquals($expected, $result);

        $expected = new stdClass;
        $result   = $dependencies->initialize($expected);
        $this->assertEquals($expected, $result);

        $expected = new stdClass;
        $result   = $dependencies->initialize(function () {
            return new stdClass;
        });
        $this->assertEquals($expected, $result);

        $expected = 'hello world';
        $result   = $dependencies->initialize(function ($message) {
            return 'hello ' . $message;
        }, ['world']);

        $this->assertEquals($expected, $result);
    }

    public function testCallClosure()
    {
        $dependencies = new Dependencies;
        $dependencies->set('Foo', 'Kirby\Toolkit\DI\Foo');
        $dependencies->set('Bar', 'Kirby\Toolkit\DI\Bar');

        // simple
        $closure = function (Foo $foo, Bar $bar) {
            $this->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
            $this->assertInstanceOf('Kirby\Toolkit\DI\Bar', $bar);
        };

        $dependencies->call($closure);

        // with arguments
        $closure = function ($a, Foo $foo, Bar $bar, $b) {
            $this->assertEquals('a', $a);
            $this->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
            $this->assertInstanceOf('Kirby\Toolkit\DI\Bar', $bar);
            $this->assertEquals('b', $b);
        };

        $dependencies->call($closure, [
            'a' => 'a',
            'b' => 'b'
        ]);

        // with new $this
        $foo     = new Foo;
        $phpunit = $this;

        $closure = function ($a, Foo $foo, Bar $bar, $b) use ($phpunit) {
            $phpunit->assertEquals('a', $a);
            $phpunit->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
            $phpunit->assertInstanceOf('Kirby\Toolkit\DI\Bar', $bar);
            $phpunit->assertEquals('b', $b);
            $phpunit->assertInstanceOf('Kirby\Toolkit\DI\Foo', $this);
        };

        $dependencies->call($closure, [
            'a' => 'a',
            'b' => 'b'
        ], $foo);
    }

    public function testCallMethod()
    {
        $class = new TestCaller($this);

        $dependencies = new Dependencies;
        $dependencies->set('Foo', 'Kirby\Toolkit\DI\Foo');
        $dependencies->set('Bar', 'Kirby\Toolkit\DI\Bar');

        $dependencies->call([$class, 'simple']);

        $dependencies->call([$class, 'withArguments'], [
            'a' => 'a',
            'b' => 'b'
        ]);
    }

    public function testCallWithAnonymousArgs()
    {
        $closure = function ($a, $b) {
            $this->assertEquals('a', $a);
            $this->assertEquals('b', $b);
        };

        $dependencies = new Dependencies;
        $dependencies->set('Foo', 'Kirby\Toolkit\DI\Foo');
        $dependencies->set('Bar', 'Kirby\Toolkit\DI\Bar');

        $dependencies->call($closure, ['a', 'b']);
    }

    public function testCallWithMixedArgs()
    {
        $closure = function ($a, $b, Foo $foo) {
            $this->assertEquals('a', $a);
            $this->assertEquals('b', $b);
            $this->assertInstanceOf('Kirby\Toolkit\DI\Foo', $foo);
        };

        $dependencies = new Dependencies;
        $dependencies->set('Foo', 'Kirby\Toolkit\DI\Foo');
        $dependencies->set('Bar', 'Kirby\Toolkit\DI\Bar');

        $dependencies->call($closure, ['a', 'b']);
    }

    public function testCallWithDependency()
    {
        $closure = function (StdClass $foo) {
            $this->assertInstanceOf('StdClass', $foo);
        };

        $dependencies = new Dependencies;
        $dependencies->set('StdClass', 'StdClass');
        $dependencies->call($closure);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The parameter "foo" is missing
     */
    public function testCallWithMissingDependency()
    {
        $closure = function (Foo $foo) {};

        $dependencies = new Dependencies;
        $dependencies->call($closure);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The parameter "a" is missing
     */
    public function testCallWithMissingArgs()
    {
        $closure = function ($a) {};

        $dependencies = new Dependencies;
        $dependencies->call($closure);
    }
}
