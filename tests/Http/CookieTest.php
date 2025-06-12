<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\TestCase;

class CookieTest extends TestCase
{
	protected string $cookieKey;

	public function setUp(): void
	{
		$this->cookieKey = Cookie::$key;
	}

	public function tearDown(): void
	{
		Cookie::$key = $this->cookieKey;
	}

	public function testKey(): void
	{
		$this->assertSame('KirbyHttpCookieKey', Cookie::$key);
		Cookie::$key = 'KirbyToolkitCookieKey';
		$this->assertSame('KirbyToolkitCookieKey', Cookie::$key);
	}

	public function testLifetime(): void
	{
		$this->assertSame(253402214400, Cookie::lifetime(253402214400));
		$this->assertSame((600 + time()), Cookie::lifetime(10));
		$this->assertSame(0, Cookie::lifetime(-10));
	}

	public function testSet(): void
	{
		Cookie::set('foo', 'bar');
		$this->assertSame('171fb1229817374e4110110384cb6be060d97351+bar', $_COOKIE['foo']);
	}

	public function testForever(): void
	{
		Cookie::forever('forever', 'bar');
		$this->assertSame('171fb1229817374e4110110384cb6be060d97351+bar', $_COOKIE['forever']);
		$this->assertTrue(Cookie::exists('forever'));
	}

	public function testRemove(): void
	{
		Cookie::forever('forever', 'bar');

		$this->assertTrue(Cookie::remove('forever'));
		$this->assertFalse(isset($_COOKIE['forever']));
		$this->assertFalse(Cookie::remove('none'));
	}

	public function testExists(): void
	{
		Cookie::set('foo', 'bar');

		$this->assertTrue(Cookie::exists('foo'));
		$this->assertFalse(Cookie::exists('new'));
	}

	public function testGet(): void
	{
		Cookie::set('foo', 'bar');

		$this->assertSame('bar', Cookie::get('foo'));
		$this->assertSame('some amazing default', Cookie::get('does_not_exist', 'some amazing default'));
		$this->assertSame($_COOKIE, Cookie::get());
	}

	public function testGetSetTrack(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->assertSame([], $app->response()->usesCookies());

		Cookie::set('foo', 'fooo');
		Cookie::get('bar');

		$this->assertSame(['foo', 'bar'], $app->response()->usesCookies());
	}

	public function testParse(): void
	{
		// valid
		$_COOKIE['foo'] = '171fb1229817374e4110110384cb6be060d97351+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// no value
		$_COOKIE['foo'] = '11d325720298d99f538012e590502154905b56e1+';
		$this->assertSame('', Cookie::get('foo'));
		$_COOKIE['foo'] = '171fb1229817374e4110110384cb6be060d97351+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// value with a plus sign
		$_COOKIE['foo'] = '04c23eb787bda27a65843cf7e474be5eb77f4807+bar+baz';
		$this->assertSame('bar+baz', Cookie::get('foo'));

		// separator missing
		$_COOKIE['foo'] = '171fb1229817374e4110110384cb6be060d97351';
		$this->assertNull(Cookie::get('foo'));
		$_COOKIE['foo'] = '171fb1229817374e4110110384cb6be060d97351+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// no hash
		$_COOKIE['foo'] = '+bar';
		$this->assertNull(Cookie::get('foo'));
		$_COOKIE['foo'] = '171fb1229817374e4110110384cb6be060d97351+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// wrong hash
		$_COOKIE['foo'] = '040df854f89c9f9ca3490fb950c91ad9aa304c97+bar';
		$this->assertNull(Cookie::get('foo'));
	}
}
