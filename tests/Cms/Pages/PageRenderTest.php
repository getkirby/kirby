<?php

namespace Kirby\Cms;

use Kirby\Cache\Value;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @covers \Kirby\Cms\Page::cacheId
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
				'index'       => static::TMP,
				'controllers' => static::FIXTURES . '/controllers',
				'templates'   => static::FIXTURES . '/templates'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'default',
						'template' => 'cache-default'
					],
					[
						'slug'     => 'data',
						'template' => 'cache-data'
					],
					[
						'slug'     => 'expiry',
						'template' => 'cache-expiry'
					],
					[
						'slug'     => 'metadata',
						'template' => 'cache-metadata',
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
						'slug'     => 'representation',
						'template' => 'representation'
					],
					[
						'slug'     => 'invalid',
						'template' => 'invalid',
					],
					[
						'slug'     => 'controller',
						'template' => 'controller',
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

	public function testCacheMetadata()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('metadata');

		$this->assertNull($cache->retrieve('metadata.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);
		$this->assertSame(202, $this->app->response()->code());
		$this->assertSame(['Cache-Control' => 'private'], $this->app->response()->headers());
		$this->assertSame('text/plain', $this->app->response()->type());

		// reset the Kirby Responder object
		$this->setUp();
		$this->assertNull($this->app->response()->code());
		$this->assertSame([], $this->app->response()->headers());
		$this->assertNull($this->app->response()->type());

		// ensure the Responder object is restored from cache
		$html2 = $this->app->page('metadata')->render();
		$this->assertSame($html1, $html2);
		$this->assertSame(202, $this->app->response()->code());
		$this->assertSame(['Cache-Control' => 'private'], $this->app->response()->headers());
		$this->assertSame('text/plain', $this->app->response()->type());
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
		$this->assertStringStartsWith('This is a test:', $html2);
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
		$this->assertStringStartsWith('This is a test:', $html2);
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
		$this->assertStringStartsWith('This is a test:', $html2);
		$this->assertNotSame($html1, $html2);
	}

	public function testCacheDataInitial()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('data');

		$this->assertNull($cache->retrieve('data.html'));

		$html = $page->render(['test' => 'custom test']);
		$this->assertStringStartsWith('This is a custom test:', $html);

		$this->assertNull($cache->retrieve('data.html'));
	}

	public function testCacheDataPreCached()
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('data');

		$this->assertNull($cache->retrieve('data.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$value = $cache->retrieve('data.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());

		$html2 = $page->render(['test' => 'custom test']);
		$this->assertStringStartsWith('This is a custom test:', $html2);

		// cache still stores the non-custom result
		$value = $cache->retrieve('data.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());
	}

	public function testRepresentationDefault()
	{
		$page = $this->app->page('representation');

		$this->assertSame('<html>Some HTML: representation</html>', $page->render());
	}

	public function testRepresentationOverride()
	{
		$page = $this->app->page('representation');

		$this->assertSame('<html>Some HTML: representation</html>', $page->render(contentType: 'html'));
		$this->assertSame('{"some json": "representation"}', $page->render(contentType: 'json'));
	}

	public function testRepresentationMissing()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The content representation cannot be found');

		$page = $this->app->page('representation');
		$page->render(contentType: 'txt');
	}

	public function testTemplateMissing()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The default template does not exist');

		$page = $this->app->page('invalid');
		$page->render();
	}

	public function testController()
	{
		$page = $this->app->page('controller');

		$this->assertSame('Data says TEST: controller and default!', $page->render());
		$this->assertSame('Data says TEST: controller and custom!', $page->render(['test' => 'override', 'test2' => 'custom']));
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
