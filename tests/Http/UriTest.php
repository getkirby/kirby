<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
	protected $example1 = 'https://getkirby.com';
	protected $example2 = 'https://testuser:weakpassword@getkirby.com:3000/docs/getting-started/with:kirby/?q=awesome#top';

	protected function setUp(): void
	{
		App::destroy();
		Uri::$current = null;
	}

	public function testClone()
	{
		$uri = new Uri([
			'host' => 'getkirby.com',
			'path' => 'test'
		]);

		$clone = $uri->clone([
			'path'  => 'yay',
			'query' => ['foo' => 'bar']
		]);

		$this->assertEquals('http://getkirby.com/test', $uri->toString());
		$this->assertEquals('http://getkirby.com/yay?foo=bar', $clone->toString());
	}

	public function testCurrent()
	{
		new App([
			'cli' => false,
			'options' => [
				'url' => 'https://getkirby.com'
			],
			'server' => [
				'REQUEST_URI' => '/docs/reference'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('https://getkirby.com/docs/reference', $uri->toString());
	}

	public function testCurrentInCli()
	{
		$uri = Uri::current();
		$this->assertEquals('/', $uri->toString());
	}

	public function testCurrentWithCustomObject()
	{
		Uri::$current = $uri = new Uri('/');

		$this->assertSame($uri, Uri::current());
	}

	public function testCurrentWithRequestUri()
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => '/a/b'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/a/b', $uri->toString());
	}

	public function testCurrentWithHostAndPathInRequestUri()
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => 'http://ktest.loc/a/b'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/a/b', $uri->toString());
	}

	public function testCurrentWithHostAndSchemeInRequestUri()
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => 'http://ktest.loc/'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/', $uri->toString());
	}

	public function testCurrentWithHostInRequestUri()
	{
		new App([
			'cli' => false,
			'server' => [
				'REQUEST_URI' => 'http://ktest.loc/a/b/ktest.loc'
			]
		]);

		$uri = Uri::current();
		$this->assertSame('/a/b/ktest.loc', $uri->toString());
	}

	public function testValidScheme()
	{
		$url = new Uri();

		$url->setScheme('http');
		$this->assertSame('http', $url->scheme());

		$url->setScheme('https');
		$this->assertSame('https', $url->scheme());
	}

	public function testIndex()
	{
		new App([
			'cli' => false,
			'options' => [
				'url' => 'https://getkirby.com'
			]
		]);

		$uri = Uri::index();
		$this->assertSame('https://getkirby.com', $uri->toString());
	}

	public function testIndexInCli()
	{
		$uri = Uri::index();
		$this->assertSame('/', $uri->toString());
	}

	public function testInvalidScheme()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid URL scheme: abc');

		$url = new Uri();
		$url->setScheme('abc');
	}

	public function testValidHost()
	{
		$url = new Uri();

		$url->setHost('getkirby.com');
		$this->assertEquals('getkirby.com', $url->host());
	}

	public function testMissingHost()
	{
		$url = new Uri(['host' => false]);
		$this->assertEquals(null, $url->host());
	}

	public function testIsAbsolute()
	{
		$url = new Uri(['host' => 'localhost']);
		$this->assertTrue($url->isAbsolute());
	}

	public function testIsNotAbsolute()
	{
		$url = new Uri();
		$this->assertFalse($url->isAbsolute());
	}

	public function testValidPort()
	{
		$url = new Uri(['port' => 1234]);
		$this->assertEquals(1234, $url->port());

		$url = new Uri(['port' => null]);
		$this->assertEquals(null, $url->port());
	}

	public function testZeroPort()
	{
		$url = new Uri(['port' => 0]);
		$this->assertEquals(null, $url->port());
	}

	public function testInvalidPortFormat1()
	{
		$this->expectException('TypeError');

		$url = new Uri(['port' => 'a']);
	}

	public function testInvalidPortFormat2()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid port format: 12010210210');

		$url = new Uri(['port' => 12010210210]);
	}

	public function testValidUsername()
	{
		$url = new Uri(['username' => 'testuser']);
		$this->assertEquals('testuser', $url->username());

		$url = new Uri(['username' => null]);
		$this->assertEquals(null, $url->username());
	}

	public function testValidPassword()
	{
		$url = new Uri(['password' => 'weakpassword']);
		$this->assertEquals('weakpassword', $url->password());

		$url = new Uri(['password' => null]);
		$this->assertEquals(null, $url->password());
	}

	public function testValidPath()
	{
		$url = new Uri(['path' => '/a/b/c']);
		$this->assertEquals('a/b/c', $url->path()->toString());

		$url = new Uri(['path' => ['a', 'b', 'c']]);
		$this->assertEquals('a/b/c', $url->path()->toString());

		$url = new Uri(['path' => null]);
		$this->assertTrue($url->path()->isEmpty());
	}

	public function testValidQuery()
	{
		$url = new Uri(['query' => 'foo=bar']);
		$this->assertEquals('foo=bar', $url->query()->toString());

		$url = new Uri(['query' => '?foo=bar']);
		$this->assertEquals('foo=bar', $url->query()->toString());

		$url = new Uri(['query' => ['foo' => 'bar']]);
		$this->assertEquals('foo=bar', $url->query()->toString());

		$url = new Uri(['query' => null]);
		$this->assertTrue($url->query()->isEmpty());
	}

	public function testValidFragment()
	{
		$url = new Uri(['fragment' => 'top']);
		$this->assertEquals('top', $url->fragment());

		$url = new Uri(['fragment' => '#top']);
		$this->assertEquals('top', $url->fragment());

		$url = new Uri(['fragment' => null]);
		$this->assertEquals(null, $url->fragment());
	}

	public function testAuth()
	{
		$url = new Uri(['username' => 'testuser', 'password' => 'weakpassword']);
		$this->assertEquals('testuser:weakpassword', $url->auth());
	}

	public function testBase()
	{
		$url = new Uri(['scheme' => 'https', 'host' => 'getkirby.com']);
		$this->assertEquals('https://getkirby.com', $url->base());

		$url->username = 'testuser';
		$url->password = 'weakpassword';

		$this->assertEquals('https://testuser:weakpassword@getkirby.com', $url->base());

		$url->port = 3000;
		$this->assertEquals('https://testuser:weakpassword@getkirby.com:3000', $url->base());
	}

	public function testBaseWithoutHost()
	{
		$url = new Uri();
		$this->assertEquals(null, $url->base());
	}

	public function testToArray()
	{
		$url = new Uri($this->example2);

		$this->assertEquals([
			'scheme'   => 'https',
			'host'     => 'getkirby.com',
			'port'     => 3000,
			'path'     => ['docs', 'getting-started'],
			'username' => 'testuser',
			'password' => 'weakpassword',
			'query'    => ['q' => 'awesome'],
			'fragment' => 'top',
			'params'   => [
				'with' => 'kirby',
			],
			'slash'    => true,
		], $url->toArray());
	}

	public function buildProvider()
	{
		return [
			// basic 1:1 tests
			[$this->example1, [], $this->example1],
			[$this->example2, [], $this->example2],

			// relative path + adding params
			[
				'/search',
				[
					'params' => ['page' => 2],
					'query'  => ['q' => 'something']
				],
				'/search/page:2?q=something'
			],

			// path + adding params + query
			[
				'https://getkirby.com/search',
				[
					'params' => ['page' => 2],
					'query'  => ['q' => 'something']
				],
				'https://getkirby.com/search/page:2?q=something'
			],

			// path + params + query
			[
				'https://getkirby.com/search?q=something',
				[
					'params' => ['page' => 2]
				],
				'https://getkirby.com/search/page:2?q=something'
			],

			// path + slash + params + query
			[
				'https://getkirby.com/search/?q=something',
				[
					'params' => ['page' => 2]
				],
				'https://getkirby.com/search/page:2/?q=something'
			],

			// params replacement
			[
				'https://getkirby.com/search/page:2?q=something',
				[
					'params' => ['page' => 3]
				],
				'https://getkirby.com/search/page:3?q=something'
			],

			// params removal without slash
			[
				'https://getkirby.com/search/page:2?q=something',
				[
					'params' => []
				],
				'https://getkirby.com/search?q=something'
			],

			// params removal with slash
			[
				'https://getkirby.com/search/page:2/?q=something',
				[
					'params' => []
				],
				'https://getkirby.com/search/?q=something'
			],

			// URL with disabled params (treated as normal path)
			[
				'https://getkirby.com/search/page:2/?q=something',
				[
					'params' => false
				],
				'https://getkirby.com/search/page:2/?q=something'
			],

			// URL with disabled params without slash
			[
				'https://getkirby.com/search/page:2?q=something',
				[
					'params' => false
				],
				'https://getkirby.com/search/page:2?q=something'
			],
		];
	}

	/**
	 * @dataProvider buildProvider
	 */
	public function testToString(string $url, array $props, string $expected)
	{
		$url = new Uri($url, $props);
		$this->assertSame($expected, $url->toString());
		$this->assertSame($expected, (string)$url);
	}

	public function testConstructParamsDisabled()
	{
		// with slash
		$url = new Uri('https://getkirby.com/search/page:2/?q=something', ['params' => false]);
		$this->assertTrue($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('search/page:2', $url->path()->toString());

		// without slash
		$url = new Uri('https://getkirby.com/search/page:2?q=something', ['params' => false]);
		$this->assertFalse($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('search/page:2', $url->path()->toString());

		// from array path
		$url = new Uri(['path' => ['search', 'page:2'], 'params' => false]);
		$this->assertFalse($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('search/page:2', $url->path()->toString());

		// without path
		$url = new Uri(['params' => false]);
		$this->assertFalse($url->slash());
		$this->assertSame('', $url->params()->toString());
		$this->assertSame('', $url->path()->toString());
	}

	public function testHttps()
	{
		$url = new Uri(['scheme' => 'http']);
		$this->assertFalse($url->https());

		$url = new Uri(['scheme' => 'https']);
		$this->assertTrue($url->https());
	}

	public function testHasFragment()
	{
		$uri = new Uri('https://getkirby.com/#footer');
		$this->assertTrue($uri->hasFragment());

		$uri = new Uri('https://getkirby.com');
		$this->assertFalse($uri->hasFragment());
	}

	public function testHasPath()
	{
		$uri = new Uri('https://getkirby.com/docs');
		$this->assertTrue($uri->hasPath());

		$uri = new Uri('https://getkirby.com');
		$this->assertFalse($uri->hasPath());
	}

	public function testHasQuery()
	{
		$uri = new Uri('https://getkirby.com?search=foo');
		$this->assertTrue($uri->hasQuery());

		$uri = new Uri('https://getkirby.com');
		$this->assertFalse($uri->hasQuery());
	}
}
