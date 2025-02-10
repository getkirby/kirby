<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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
		$route = new Route('/', 'POST', $func = fn () => 'test');

		$this->assertSame('', $route->pattern());
		$this->assertSame('POST', $route->method());
		$this->assertSame($func, $route->action());
		$this->assertSame('test', $route->action()->call($route));
	}

	public function testName()
	{
		$route = new Route('a', 'GET', function () {
		}, [
			'name' => 'test'
		]);

		$this->assertSame('test', $route->name());
	}

	public function testAttributes()
	{
		$route = new Route('a', 'GET', function () {
		}, $attributes = [
			'a' => 'a',
			'b' => 'b'
		]);

		$this->assertSame($attributes, $route->attributes());
	}

	public function testAttributesGetter()
	{
		$route = new Route('a', 'GET', function () {
		}, [
			'a' => 'a'
		]);

		$this->assertSame('a', $route->a());
		$this->assertNull($route->b());
	}

	public function testPattern()
	{
		$route = $this->_route();
		$this->assertSame('a', $route->pattern());
	}

	public function testMethod()
	{
		$route = $this->_route();
		$this->assertSame('GET', $route->method());
	}

	public function testRegex()
	{
		$route = $this->_route();
		$this->assertSame('a/(-?[0-9]+)/b', $route->regex('a/(:num)/b'));
		$this->assertSame('a/([a-zA-Z]+)/b', $route->regex('a/(:alpha)/b'));
		$this->assertSame('a/([a-zA-Z0-9]+)/b', $route->regex('a/(:alphanum)/b'));
		$this->assertSame('a/([a-zA-Z0-9\.\-_%= \+\@\(\)]+)/b', $route->regex('a/(:any)/b'));
		$this->assertSame('a/(.*)', $route->regex('a/(:all)'));
		$this->assertSame('a(?:/(-?[0-9]+))?', $route->regex('a/(:num?)'));
		$this->assertSame('a(?:/([a-zA-Z]+))?', $route->regex('a/(:alpha?)'));
		$this->assertSame('a(?:/([a-zA-Z0-9]+))?', $route->regex('a/(:alphanum?)'));
		$this->assertSame('a(?:/([a-zA-Z0-9\.\-_%= \+\@\(\)]+))?', $route->regex('a/(:any?)'));
		$this->assertSame('a(?:/(.*))?', $route->regex('a/(:all?)'));
	}

	public static function patternProvider(): array
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

	#[DataProvider('patternProvider')]
	public function testParse(string $pattern, string $input, bool $match)
	{
		$route = $this->_route();

		if ($match === true) {
			// required
			$this->assertSame([$input], $route->parse('(' . $pattern . ')', $input));
			// optional
			$this->assertSame([$input], $route->parse('/(' . $pattern . '?)', '/' . $input));
		} else {
			// required
			$this->assertFalse($route->parse('(' . $pattern . ')', $input));
			// optional
			$this->assertFalse($route->parse('/(' . $pattern . '?)', '/' . $input));
		}
	}
}
