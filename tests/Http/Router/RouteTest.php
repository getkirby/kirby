<?php

namespace Kirby\Http\Router;

use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function _route()
    {
        $route = new Route('a', 'GET', function () {});
        return $route;
    }

    public function testConstruct()
    {
        $route = new Route('/', 'POST', $func = function () {
            return 'test';
        });

        $this->assertEquals(['/'], $route->pattern());
        $this->assertEquals(['POST'], $route->method());
        $this->assertEquals($func, $route->action());
        $this->assertEquals('test', $route->action()->call($route));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid pattern type
     */
    public function testPattern()
    {
        $route = $this->_route();
        $this->assertEquals(['a'], $route->pattern());
        $this->assertEquals(['a', 'b'], $route->pattern(['a', 'b']));
        $route->pattern(42);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid method name
     */
    public function testMethod()
    {
        $route = $this->_route();
        $this->assertEquals(['GET'], $route->method());
        $this->assertEquals(['GET', 'POST'], $route->method('GET|POST'));
        $this->assertEquals(['GET', 'POST'], $route->method(['GET', 'POST']));
        $this->assertEquals([
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'DELETE',
            'CONNECT',
            'OPTIONS',
            'TRACE',
            'PATCH'
        ], $route->method('ALL'));

        $route->method('GET|KIRBY');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid method type
     */
    public function testMethodInvalidType()
    {
        $route = $this->_route();
        $route->method(42);
    }

    public function testRegex()
    {
        $route = $this->_route();
        $this->assertEquals('a/([0-9]+)/b', $route->regex('a/(:num)/b'));
        $this->assertEquals('a/([a-zA-Z]+)/b', $route->regex('a/(:alpha)/b'));
        $this->assertEquals('a/([a-zA-Z0-9\.\-_%= ]+)/b', $route->regex('a/(:any)/b'));
        $this->assertEquals('a/(.*)', $route->regex('a/(:all)'));
        $this->assertEquals('a(?:/([0-9]+))?', $route->regex('a/(:num?)'));
        $this->assertEquals('a(?:/([a-zA-Z]+))?', $route->regex('a/(:alpha?)'));
        $this->assertEquals('a(?:/([a-zA-Z0-9\.\-_%= ]+))?', $route->regex('a/(:any?)'));
        $this->assertEquals('a(?:/(.*))?', $route->regex('a/(:all?)'));
    }

    public function testArguments()
    {
        $route = $this->_route();
        $this->assertFalse($route->arguments('a/b/c', 'a/15/c'));
        $this->assertEquals(['15'], $route->arguments('a/(:num)/c', 'a/15/c'));
        $this->assertEquals(['15', 'c/d'], $route->arguments('(:num)/(:all)', '15/c/d'));
        $this->assertFalse($route->arguments('(:kirby)', 'test'));
    }
}
