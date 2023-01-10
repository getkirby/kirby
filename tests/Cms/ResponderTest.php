<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Cms\Responder
 */
class ResponderTest extends TestCase
{
	public function setUp(): void
	{
		$this->kirby([
			'urls' => [
				'index' => 'https://getkirby.test'
			]
		]);
	}

	public function tearDown(): void
	{
		unset($_COOKIE['foo'], $_SERVER['HTTP_AUTHORIZATION']);
	}

	/**
	 * @covers ::cache
	 */
	public function testCache()
	{
		$responder = new Responder();
		$this->assertTrue($responder->cache());

		$this->assertSame($responder, $responder->cache(false));
		$this->assertFalse($responder->cache());

		$this->assertSame($responder, $responder->cache(true));
		$this->assertTrue($responder->cache());
	}

	/**
	 * @covers ::cache
	 */
	public function testCacheUsesCookies()
	{
		$_COOKIE['foo'] = 'bar';

		$responder = new Responder();
		$this->assertTrue($responder->cache());

		$responder->usesCookie('foo');
		$this->assertFalse($responder->cache());
	}

	/**
	 * @covers ::cache
	 */
	public function testCacheUsesAuth()
	{
		$this->kirby([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Bearer brown-bearer'
			]
		]);

		$responder = new Responder();
		$this->assertTrue($responder->cache());

		$responder->usesAuth(true);
		$this->assertFalse($responder->cache());
	}

	/**
	 * @covers ::expires
	 */
	public function testExpires()
	{
		$responder = new Responder();
		$this->assertNull($responder->expires());

		// minutes
		$this->assertSame($responder, $responder->expires(60 * 24));
		$this->assertSame(MockTime::$time + 60 * 60 * 24, $responder->expires());

		// explicit timestamp
		$this->assertSame($responder, $responder->expires(1234567890));
		$this->assertSame(1234567890, $responder->expires());

		// shorter expiry is always possible
		$this->assertSame($responder, $responder->expires(1234567889));
		$this->assertSame(1234567889, $responder->expires());

		// longer expiry only explicitly
		$this->assertSame($responder, $responder->expires(1234567890));
		$this->assertSame(1234567889, $responder->expires());

		$this->assertSame($responder, $responder->expires(1234567890, true));
		$this->assertSame(1234567890, $responder->expires());

		// getter on null input
		$this->assertSame(1234567890, $responder->expires(null));
		$this->assertSame(1234567890, $responder->expires());

		// but unset explicitly
		$this->assertSame($responder, $responder->expires(null, true));
		$this->assertNull($responder->expires());

		// string value parsing
		$this->assertSame($responder, $responder->expires('2021-01-01'));
		$this->assertSame(1609459200, $responder->expires());

		// rules still apply to string values
		$this->assertSame($responder, $responder->expires('2020-12-31'));
		$this->assertSame(1609372800, $responder->expires());
		$this->assertSame($responder, $responder->expires('2021-01-01'));
		$this->assertSame(1609372800, $responder->expires());
		$this->assertSame($responder, $responder->expires('2021-01-01', true));
		$this->assertSame(1609459200, $responder->expires());
	}

	/**
	 * @covers ::expires
	 */
	public function testExpiresInvalidString()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid time string "abcde"');

		$responder = new Responder();
		$responder->expires('abcde');
	}

	/**
	 * @covers ::fromArray
	 */
	public function testFromArray()
	{
		$responder = new Responder();
		$responder->fromArray([
			'body'        => 'Lorem ipsum',
			'cache'       => false,
			'code'        => 301,
			'expires'     => 1234567890,
			'headers'     => ['Location' => 'https://example.com'],
			'type'        => 'text/plain',
			'usesAuth'    => true,
			'usesCookies' => ['foo'],
		]);

		$this->assertSame('Lorem ipsum', $responder->body());
		$this->assertSame(301, $responder->code());
		$this->assertFalse($responder->cache());
		$this->assertSame(1234567890, $responder->expires());
		$this->assertSame([
			'Vary' => 'Authorization, Cookie',
			'Location' => 'https://example.com'
		], $responder->headers());
		$this->assertSame('text/plain', $responder->type());
		$this->assertTrue($responder->usesAuth());
		$this->assertSame(['foo'], $responder->usesCookies());
	}

	/**
	 * @covers ::header
	 */
	public function testHeader()
	{
		$responder = new Responder();

		// getter for non-existing header
		$this->assertNull($responder->header('Cache-Control'));

		// simple setter and getter
		$this->assertSame($responder, $responder->header('Cache-Control', 'private'));
		$this->assertSame('private', $responder->header('Cache-Control'));

		// unset existing header
		$this->assertSame($responder, $responder->header('Cache-Control', false));
		$this->assertNull($responder->header('Cache-Control'));

		// unset non-existing header
		$this->assertSame($responder, $responder->header('Location', false));
		$this->assertNull($responder->header('Location'));

		// lazy setter
		$this->assertSame($responder, $responder->header('Cache-Control', 'private', true));
		$this->assertSame('private', $responder->header('Cache-Control'));
		$this->assertSame($responder, $responder->header('Cache-Control', 'no-cache', true));
		$this->assertSame('private', $responder->header('Cache-Control'));

		// modified caching behavior (not active)
		$responder->headers([]);
		$responder->usesAuth(true);
		$responder->usesCookie('foo');
		$this->assertNull($responder->header('Cache-Control'));
		$this->assertSame('Authorization, Cookie', $responder->header('Vary'));

		// modified caching behavior (active)
		$_COOKIE['foo'] = 'bar';
		$this->assertSame('no-store, private', $responder->header('Cache-Control'));
		$this->assertNull($responder->header('Vary'));

		// never override custom header value
		$responder->header('Cache-Control', 'private');
		$this->assertSame('private', $responder->header('Cache-Control'));
	}

	/**
	 * @covers ::headers
	 */
	public function testHeaders()
	{
		$responder = new Responder();
		$this->assertSame([], $responder->headers());

		$this->assertSame($responder, $responder->headers($headers = ['Foo' => 'foo', 'Bar' => 'bar']));
		$this->assertSame($headers, $responder->headers());

		$this->assertSame($responder, $responder->headers($headers = ['Foo' => 'bar']));
		$this->assertSame($headers, $responder->headers());
	}

	/**
	 * @covers ::headers
	 */
	public function testHeadersCacheBehavior()
	{
		$responder = new Responder();
		$this->assertSame([], $responder->headers());

		// non-active (auth)
		$responder->usesAuth(true);
		$responder->usesCookies([]);
		$this->assertSame(['Vary' => 'Authorization'], $responder->headers());

		// non-active (cookies)
		$responder->usesAuth(false);
		$responder->usesCookies(['foo']);
		$this->assertSame(['Vary' => 'Cookie'], $responder->headers());

		// non-active (both)
		$responder->usesAuth(true);
		$responder->usesCookies(['foo']);
		$this->assertSame(['Vary' => 'Authorization, Cookie'], $responder->headers());

		// active
		$_COOKIE['foo'] = 'bar';
		$this->assertSame(['Cache-Control' => 'no-store, private'], $responder->headers());

		// never override custom header value
		$responder->header('Cache-Control', 'private');
		$this->assertSame(['Cache-Control' => 'private'], $responder->headers());
	}

	/**
	 * @covers ::isPrivate
	 */
	public function testIsPrivate()
	{
		$responder = new Responder();

		// no dynamic data in environment
		$this->assertFalse($responder->isPrivate(true, []));
		$this->assertFalse($responder->isPrivate(true, ['foo']));
		$this->assertFalse($responder->isPrivate(true, ['bar']));
		$this->assertFalse($responder->isPrivate(true, ['foo', 'bar']));
		$this->assertFalse($responder->isPrivate(false, ['foo']));
		$this->assertFalse($responder->isPrivate(false, ['foo', 'bar']));
		$this->assertFalse($responder->isPrivate(false, ['bar']));
		$this->assertFalse($responder->isPrivate(false, []));

		// with dynamic data in environment
		$_COOKIE['foo'] = 'bar';
		$this->kirby([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Bearer brown-bearer'
			]
		]);

		$this->assertTrue($responder->isPrivate(true, []));
		$this->assertTrue($responder->isPrivate(true, ['foo']));
		$this->assertTrue($responder->isPrivate(true, ['bar']));
		$this->assertTrue($responder->isPrivate(true, ['foo', 'bar']));
		$this->assertTrue($responder->isPrivate(false, ['foo']));
		$this->assertTrue($responder->isPrivate(false, ['foo', 'bar']));
		$this->assertFalse($responder->isPrivate(false, ['bar']));
		$this->assertFalse($responder->isPrivate(false, []));
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		$responder = new Responder();
		$responder->fromArray([
			'body'        => 'Lorem ipsum',
			'cache'       => false,
			'code'        => 301,
			'expires'     => 1234567890,
			'headers'     => ['Location' => 'https://example.com'],
			'type'        => 'text/plain',
			'usesAuth'    => true,
			'usesCookies' => ['foo'],
		]);

		$this->assertSame([
			'body'    => 'Lorem ipsum',
			'code'    => 301,
			'headers' => [
				'Vary'     => 'Authorization, Cookie',
				'Location' => 'https://example.com'
			],
			'type'    => 'text/plain'
		], $responder->toArray());
	}

	/**
	 * @covers ::usesAuth
	 */
	public function testUsesAuth()
	{
		$responder = new Responder();
		$this->assertFalse($responder->usesAuth());

		$this->assertSame($responder, $responder->usesAuth(true));
		$this->assertTrue($responder->usesAuth());

		$this->assertSame($responder, $responder->usesAuth(false));
		$this->assertFalse($responder->usesAuth());
	}

	/**
	 * @covers ::usesCookie
	 */
	public function testUsesCookie()
	{
		$responder = new Responder();
		$this->assertSame([], $responder->usesCookies());

		$responder->usesCookie('foo');
		$this->assertSame(['foo'], $responder->usesCookies());

		$responder->usesCookie('bar');
		$this->assertSame(['foo', 'bar'], $responder->usesCookies());

		// deduplication
		$responder->usesCookie('bar');
		$this->assertSame(['foo', 'bar'], $responder->usesCookies());
	}

	/**
	 * @covers ::usesCookies
	 */
	public function testUsesCookies()
	{
		$responder = new Responder();
		$this->assertSame([], $responder->usesCookies());

		$this->assertSame($responder, $responder->usesCookies($cookies = ['foo', 'bar']));
		$this->assertSame($cookies, $responder->usesCookies());

		$this->assertSame($responder, $responder->usesCookies($cookies = ['bar']));
		$this->assertSame($cookies, $responder->usesCookies());
	}
}
