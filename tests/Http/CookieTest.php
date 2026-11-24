<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cookie::class)]
class CookieTest extends TestCase
{
	protected string $cookieKey;
	protected array $cookies;

	protected function setUp(): void
	{
		$this->cookieKey = Cookie::$key;
		$this->cookies   = $_COOKIE;
	}

	protected function tearDown(): void
	{
		Cookie::$key = $this->cookieKey;
		$_COOKIE     = $this->cookies;

		App::destroy();
	}

	public function testExists(): void
	{
		Cookie::set('foo', 'bar');

		$this->assertTrue(Cookie::exists('foo'));
		$this->assertFalse(Cookie::exists('new'));
	}

	public function testForever(): void
	{
		Cookie::forever('forever', 'bar');
		$this->assertSame('706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar', $_COOKIE['forever']);
		$this->assertTrue(Cookie::exists('forever'));
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

	public function testKey(): void
	{
		$this->assertSame('KirbyHttpCookieKey', Cookie::$key);
		Cookie::$key = 'KirbyToolkitCookieKey';
		$this->assertSame('KirbyToolkitCookieKey', Cookie::$key);
	}

	public function testKeyFromAppOption(): void
	{
		Cookie::set('foo', 'bar');
		$this->assertSame('706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar', $_COOKIE['foo']);

		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'cookie' => [
					'key' => 'VerySecureLongRandomString'
				]
			]
		]);

		Cookie::set('foo', 'bar');
		$this->assertSame('7028f7f15eb8756bc3081575536331c577413f48b65a5bedb3624304f26752ee+bar', $_COOKIE['foo']);
	}

	public function testLifetime(): void
	{
		$this->assertSame(253402214400, Cookie::lifetime(253402214400));
		$this->assertSame((600 + time()), Cookie::lifetime(10));
		$this->assertSame(0, Cookie::lifetime(-10));
	}

	public function testParse(): void
	{
		// valid
		$_COOKIE['foo'] = '706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// no value
		$_COOKIE['foo'] = '7477dd4ba0948adb86fb45057b047798439cdaee3d676b2f0ac456f9d7d69fc4+';
		$this->assertSame('', Cookie::get('foo'));
		$_COOKIE['foo'] = '706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// value with a plus sign
		$_COOKIE['foo'] = 'd09e4bcb59f3c834f73af93494d3815b7e31035095223b493421853507349c41+bar+baz';
		$this->assertSame('bar+baz', Cookie::get('foo'));

		// separator missing
		$_COOKIE['foo'] = '706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96';
		$this->assertNull(Cookie::get('foo'));
		$_COOKIE['foo'] = '706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// no hash
		$_COOKIE['foo'] = '+bar';
		$this->assertNull(Cookie::get('foo'));
		$_COOKIE['foo'] = '706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar';
		$this->assertSame('bar', Cookie::get('foo'));

		// wrong hash
		$_COOKIE['foo'] = '5ae48911f1ad2c13e2a276a1d450333d0c61d31c91758f98ad85c94ec7a435ff+bar';
		$this->assertNull(Cookie::get('foo'));

		// legacy SHA-1 hash
		$_COOKIE['foo'] = '171fb1229817374e4110110384cb6be060d97351+bar';
		$this->assertNull(Cookie::get('foo'));
	}

	public function testRemove(): void
	{
		Cookie::forever('forever', 'bar');

		$this->assertTrue(Cookie::remove('forever'));
		$this->assertFalse(isset($_COOKIE['forever']));
		$this->assertFalse(Cookie::remove('none'));
	}

	public function testSet(): void
	{
		Cookie::set('foo', 'bar');
		$this->assertSame('706666d6d0d099f2eca6184e752ae30e43e70c436ca1d3fbec6a82877bb62a96+bar', $_COOKIE['foo']);
	}
}
