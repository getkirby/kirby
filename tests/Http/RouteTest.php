<?php

namespace Kirby\Http;

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

        $this->assertEquals('/', $route->pattern());
        $this->assertEquals('POST', $route->method());
        $this->assertEquals($func, $route->action());
        $this->assertEquals('test', $route->action()->call($route));
    }

    public function testPattern()
    {
        $route = $this->_route();
        $this->assertEquals('a', $route->pattern());
    }

    public function testMethod()
    {
        $route = $this->_route();
        $this->assertEquals('GET', $route->method());
    }

    public function testRegex()
    {
        $route = $this->_route();
        $this->assertEquals('a/([0-9]+)/b', $route->regex('a/(:num)/b'));
        $this->assertEquals('a/([a-zA-Z]+)/b', $route->regex('a/(:alpha)/b'));
        $this->assertEquals('a/([a-zA-Z0-9\.\-_%= \+\@]+)/b', $route->regex('a/(:any)/b'));
        $this->assertEquals('a/(.*)', $route->regex('a/(:all)'));
        $this->assertEquals('a(?:/([0-9]+))?', $route->regex('a/(:num?)'));
        $this->assertEquals('a(?:/([a-zA-Z]+))?', $route->regex('a/(:alpha?)'));
        $this->assertEquals('a(?:/([a-zA-Z0-9\.\-_%= \+\@]+))?', $route->regex('a/(:any?)'));
        $this->assertEquals('a(?:/(.*))?', $route->regex('a/(:all?)'));
    }

    public function testParse()
    {
        $route = $this->_route();
        $this->assertFalse($route->parse('a/b/c', 'a/15/c'));
        $this->assertEquals(['15'], $route->parse('a/(:num)/c', 'a/15/c'));
        $this->assertEquals(['15', 'c/d'], $route->parse('(:num)/(:all)', '15/c/d'));
        $this->assertFalse($route->parse('(:kirby)', 'test'));
    }
}
