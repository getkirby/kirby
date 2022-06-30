<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

class PageCacheTest extends TestCase
{
	protected $app;
	protected $tmp;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index'     => $this->tmp = __DIR__ . '/tmp',
				'templates' => __DIR__ . '/fixtures/PageCacheTest'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'default'
					],
					[
						'slug'     => 'expiry',
						'template' => 'expiry'
					],
					[
						'slug'     => 'disabled',
						'template' => 'disabled'
					],
					[
						'slug'     => 'dynamic-auth',
						'template' => 'dynamic'
					],
					[
						'slug'     => 'dynamic-cookie',
						'template' => 'dynamic'
					],
					[
						'slug'     => 'dynamic-session',
						'template' => 'dynamic'
					],
					[
						'slug'     => 'dynamic-auth-session',
						'template' => 'dynamic'
					]
				]
			],
			'options' => [
				'cache.pages' => true
			]
		]);

		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);

		unset(
			$_COOKIE['foo'],
			$_COOKIE['kirby_session'],
			$_SERVER['HTTP_AUTHORIZATION']
		);
	}

	public function requestMethodProvider()
	{
		return [
			['GET', true],
			['HEAD', true],
			['POST', false],
			['DELETE', false],
			['PATCH', false],
			['PUT', false],
		];
	}

	/**
	 * @dataProvider requestMethodProvider
	 */
	public function testRequestMethod($method, $expected)
	{
		$app = $this->app->clone([
			'request' => [
				'method' => $method
			]
		]);

		$this->assertEquals($expected, $app->page('default')->isCacheable());
	}

	/**
	 * @dataProvider requestMethodProvider
	 */
	public function testRequestData($method)
	{
		$app = $this->app->clone([
			'request' => [
				'method' => $method,
				'query'  => ['foo' => 'bar']
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
	}

	public function testRequestParams()
	{
		$app = $this->app->clone([
			'request' => [
				'url' => 'https://getkirby.com/blog/page:2'
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
	}

	public function testIgnoreId()
	{
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'ignore' => [
						'expiry'
					]
				]
			]
		]);

		$this->assertTrue($app->page('default')->isCacheable());
		$this->assertFalse($app->page('expiry')->isCacheable());
	}

	public function testIgnoreCallback()
	{
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'ignore' => function ($page) {
						return $page->id() === 'default';
					}
				]
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
		$this->assertTrue($app->page('expiry')->isCacheable());
	}

	public function testDisabledCache()
	{
		// deactivate on top level
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => false
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());

		// deactivate in array
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'active' => false
				]
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
	}

	public function testRenderCache()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('default');

		$this->assertNull($cache->retrieve('default.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$value = $cache->retrieve('default.html');
		$this->assertInstanceOf('Kirby\Cache\Value', $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());

		$html2 = $page->render();
		$this->assertSame($html1, $html2);
	}

	public function testRenderCacheCustomExpiry()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('expiry');

		$this->assertNull($cache->retrieve('expiry.html'));

		$time = $page->render();

		$value = $cache->retrieve('expiry.html');
		$this->assertInstanceOf('Kirby\Cache\Value', $value);
		$this->assertSame($time, $value->value()['html']);
		$this->assertSame((int)$time, $value->expires());
	}

	public function testRenderCacheDisabled()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('disabled');

		$this->assertNull($cache->retrieve('disabled.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$this->assertNull($cache->retrieve('disabled.html'));

		$html2 = $page->render();
		$this->assertNotSame($html1, $html2);
	}

	public function dynamicProvider(): array
	{
		return [
			['dynamic-auth', ['auth']],
			['dynamic-cookie', ['cookie']],
			['dynamic-session', ['session']],
			['dynamic-auth-session', ['auth', 'session']],
		];
	}

	/**
	 * @dataProvider dynamicProvider
	 */
	public function testRenderCacheDynamicNonActive(string $slug, array $dynamicElements)
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page($slug);

		$this->assertNull($cache->retrieve($slug . '.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$cacheValue = $cache->retrieve($slug . '.html');
		$this->assertNotNull($cacheValue);
		$this->assertSame(in_array('auth', $dynamicElements), $cacheValue->value()['usesAuth']);
		if (in_array('cookie', $dynamicElements)) {
			$this->assertSame(['foo'], $cacheValue->value()['usesCookies']);
		} elseif (in_array('session', $dynamicElements)) {
			$this->assertSame(['kirby_session'], $cacheValue->value()['usesCookies']);
		} else {
			$this->assertSame([], $cacheValue->value()['usesCookies']);
		}

		// reset the Kirby Responder object
		$this->setUp();
		$html2 = $page->render();
		$this->assertSame($html1, $html2);
	}

	/**
	 * @dataProvider dynamicProvider
	 */
	public function testRenderCacheDynamicActiveOnFirstRender(string $slug, array $dynamicElements)
	{
		$_COOKIE['foo'] = $_COOKIE['kirby_session'] = 'bar';
		$this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Bearer brown-bearer'
			]
		]);

		$cache = $this->app->cache('pages');
		$page  = $this->app->page($slug);

		$this->assertNull($cache->retrieve($slug . '.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$cacheValue = $cache->retrieve($slug . '.html');
		$this->assertNull($cacheValue);

		// reset the Kirby Responder object
		$this->setUp();
		$html2 = $page->render();
		$this->assertNotSame($html1, $html2);
	}

	/**
	 * @dataProvider dynamicProvider
	 */
	public function testRenderCacheDynamicActiveOnSecondRender(string $slug, array $dynamicElements)
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page($slug);

		$this->assertNull($cache->retrieve($slug . '.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$cacheValue = $cache->retrieve($slug . '.html');
		$this->assertNotNull($cacheValue);
		$this->assertSame(in_array('auth', $dynamicElements), $cacheValue->value()['usesAuth']);
		if (in_array('cookie', $dynamicElements)) {
			$this->assertSame(['foo'], $cacheValue->value()['usesCookies']);
		} elseif (in_array('session', $dynamicElements)) {
			$this->assertSame(['kirby_session'], $cacheValue->value()['usesCookies']);
		} else {
			$this->assertSame([], $cacheValue->value()['usesCookies']);
		}

		$_COOKIE['foo'] = $_COOKIE['kirby_session'] = 'bar';
		$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer brown-bearer';

		// reset the Kirby Responder object
		$this->setUp();
		$html2 = $page->render();
		$this->assertNotSame($html1, $html2);
	}
}
