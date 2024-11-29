<?php

namespace Kirby\Cms;

use Kirby\Cache\Value;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @covers \Kirby\Cms\Page::render
 */
class PageRenderTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/PageRenderTest';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.PageRender';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index'     => static::TMP,
				'templates' => static::FIXTURES
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'default',
						'template' => 'cache-default'
					],
					[
						'slug'     => 'expiry',
						'template' => 'cache-expiry'
					],
					[
						'slug'     => 'disabled',
						'template' => 'cache-disabled'
					],
					[
						'slug'     => 'dynamic-auth',
						'template' => 'cache-dynamic'
					],
					[
						'slug'     => 'dynamic-cookie',
						'template' => 'cache-dynamic'
					],
					[
						'slug'     => 'dynamic-session',
						'template' => 'cache-dynamic'
					],
					[
						'slug'     => 'dynamic-auth-session',
						'template' => 'cache-dynamic'
					],
					[
						'slug'      => 'bar',
						'template'  => 'hook-bar',
						'content'   => [
							'title' => 'Bar Title',
						]
					],
					[
						'slug'      => 'foo',
						'template'  => 'hook-foo',
						'content'   => [
							'title' => 'Foo Title',
						]
					]
				]
			],
			'options' => [
				'cache.pages' => true
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);

		unset(
			$_COOKIE['foo'],
			$_COOKIE['kirby_session'],
			$_SERVER['HTTP_AUTHORIZATION']
		);
	}

	public function testCache()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('default');

		$this->assertNull($cache->retrieve('default.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$value = $cache->retrieve('default.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());

		$html2 = $page->render();
		$this->assertSame($html1, $html2);
	}

	public function testCacheCustomExpiry()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('expiry');

		$this->assertNull($cache->retrieve('expiry.html'));

		$time = $page->render();

		$value = $cache->retrieve('expiry.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($time, $value->value()['html']);
		$this->assertSame((int)$time, $value->expires());
	}

	public function testCacheDisabled()
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

	public static function dynamicProvider(): array
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
	public function testCacheDynamicNonActive(string $slug, array $dynamicElements)
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
	public function testCacheDynamicActiveOnFirstRender(string $slug, array $dynamicElements)
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
	public function testCacheDynamicActiveOnSecondRender(string $slug, array $dynamicElements)
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

	public function testHookBefore()
	{
		$app = $this->app->clone([
			'hooks' => [
				'page.render:before' => function ($contentType, $data, $page) {
					$data['bar'] = 'Test';
					return $data;
				}
			]
		]);

		$page = $app->page('bar');
		$this->assertSame('Bar Title : Test', $page->render());
	}

	public function testHookAfter()
	{
		$app = $this->app->clone([
			'hooks' => [
				'page.render:after' => function ($contentType, $data, $html, $page) {
					return str_replace(':', '-', $html);
				}
			]
		]);

		$page = $app->page('foo');
		$this->assertSame('foo - Foo Title', $page->render());
	}
}
