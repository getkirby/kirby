<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function _route()
    {
        $route = new Route('a', 'GET', function () {
        });
        return $route;
    }

    public function testConstruct()
    {
        $route = new Route('/', 'POST', $func = function () {
            return 'test';
        });

        $this->assertEquals('', $route->pattern());
        $this->assertEquals('POST', $route->method());
        $this->assertEquals($func, $route->action());
        $this->assertEquals('test', $route->action()->call($route));
    }

    public function testName()
    {
        $route = new Route('a', 'GET', function () {
        }, [
            'name' => 'test'
        ]);

        $this->assertEquals('test', $route->name());
    }

    public function testAttributes()
    {
        $route = new Route('a', 'GET', function () {
        }, $attributes = [
            'a' => 'a',
            'b' => 'b'
        ]);

        $this->assertEquals($attributes, $route->attributes());
    }

    public function testAttributesGetter()
    {
        $route = new Route('a', 'GET', function () {
        }, [
            'a' => 'a'
        ]);

        $this->assertEquals('a', $route->a());
        $this->assertEquals(null, $route->b());
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
        $this->assertEquals('a/(-?[0-9]+)/b', $route->regex('a/(:num)/b'));
        $this->assertEquals('a/([a-zA-Z]+)/b', $route->regex('a/(:alpha)/b'));
        $this->assertEquals('a/([a-zA-Z0-9]+)/b', $route->regex('a/(:alphanum)/b'));
        $this->assertEquals('a/([a-zA-Z0-9\.\-_%= \+\@\(\)]+)/b', $route->regex('a/(:any)/b'));
        $this->assertEquals('a/(.*)', $route->regex('a/(:all)'));
        $this->assertEquals('a(?:/(-?[0-9]+))?', $route->regex('a/(:num?)'));
        $this->assertEquals('a(?:/([a-zA-Z]+))?', $route->regex('a/(:alpha?)'));
        $this->assertEquals('a(?:/([a-zA-Z0-9]+))?', $route->regex('a/(:alphanum?)'));
        $this->assertEquals('a(?:/([a-zA-Z0-9\.\-_%= \+\@\(\)]+))?', $route->regex('a/(:any?)'));
        $this->assertEquals('a(?:/(.*))?', $route->regex('a/(:all?)'));
    }

    public function patternProvider()
    {
        return [
            // simple strings
            [':any', 'abc', true],
            // @
            [':any', 'test@company.com', true],
            // +
            [':any', 'projects+test', true],
            // ( )
            [':any', 'metallica_(band).jpg', true],
            // spaces
            [':any', 'a b c', true],
            // alpha
            [':alpha', 'abc', true],
            // invalid alpha
            [':alpha', '123', false],
            // alphanum
            [':alphanum', 'abc123', true],
            // invalid alphanum
            [':alphanum', 'äbc123', false],
            // numbers
            [':num', '15', true],
            // negative numbers
            [':num', '-15', true],
            // invalid number
            [':num', 'a', false],
            // invalid pattern
            [':kirby', 'kirby', false]
        ];
    }

    /**
     * @dataProvider patternProvider
     */
    public function testParse($pattern, $input, $match)
    {
        $route = $this->_route();

        if ($match === true) {
            // required
            $this->assertEquals([$input], $route->parse('(' . $pattern . ')', $input));
            // optional
            $this->assertEquals([$input], $route->parse('/(' . $pattern . '?)', '/' . $input));
        } else {
            // required
            $this->assertFalse($route->parse('(' . $pattern . ')', $input));
            // optional
            $this->assertFalse($route->parse('/(' . $pattern . '?)', '/' . $input));
        }
    }
}
