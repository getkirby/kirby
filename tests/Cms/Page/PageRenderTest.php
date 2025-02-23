<?php

namespace Kirby\Cms;

use Kirby\Cache\Value;

use Kirby\Content\VersionId;
use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Page::class)]
class PageRenderTest extends NewModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/PageRenderTest';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.PageRender';

	public function setUp(): void
	{
		parent::setUp();

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
					],
					[
						'slug'     => 'version',
						'template' => 'version'
					],
					[
						'slug'     => 'version-exception',
						'template' => 'version-exception'
					],
					[
						'slug'     => 'version-recursive',
						'template' => 'version-recursive'
					]
				],
				'drafts' => [
					[
						'slug'     => 'version-draft',
						'template' => 'version',
						'children' => [
							[
								'slug'     => 'a-child',
								'template' => 'version'
							]
						]
					],
				]
			],
			'options' => [
				'cache.pages' => true
			]
		]);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		unset(
			$_COOKIE['foo'],
			$_COOKIE['kirby_session'],
			$_SERVER['HTTP_AUTHORIZATION']
		);
	}

	public static function requestMethodProvider(): array
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

	#[DataProvider('requestMethodProvider')]
	public function testIsCacheableRequestMethod(
		string $method,
		bool $expected
	): void {
		$app = $this->app->clone([
			'request' => [
				'method' => $method
			]
		]);

		$this->assertSame($expected, $app->page('default')->isCacheable());
		$this->assertSame($expected, $app->page('default')->isCacheable(VersionId::latest()));
		$this->assertFalse($app->page('default')->isCacheable(VersionId::changes()));
	}

	#[DataProvider('requestMethodProvider')]
	public function testIsCacheableRequestData(
		string $method
	): void {
		$app = $this->app->clone([
			'request' => [
				'method' => $method,
				'query'  => ['foo' => 'bar']
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
	}

	public function testIsCacheableRequestParams(): void
	{
		$app = $this->app->clone([
			'request' => [
				'url' => 'https://getkirby.com/blog/page:2'
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
	}

	public function testIsCacheableIgnoreId(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'ignore' => [
						'data'
					]
				]
			]
		]);

		$this->assertTrue($app->page('default')->isCacheable());
		$this->assertTrue($app->page('default')->isCacheable(VersionId::latest()));
		$this->assertFalse($app->page('default')->isCacheable(VersionId::changes()));
		$this->assertFalse($app->page('data')->isCacheable());
		$this->assertFalse($app->page('data')->isCacheable(VersionId::latest()));
		$this->assertFalse($app->page('data')->isCacheable(VersionId::changes()));
	}

	public function testIsCacheableIgnoreCallback(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'ignore' => fn ($page) => $page->id() === 'default'
				]
			]
		]);

		$this->assertFalse($app->page('default')->isCacheable());
		$this->assertFalse($app->page('default')->isCacheable(VersionId::latest()));
		$this->assertFalse($app->page('default')->isCacheable(VersionId::changes()));
		$this->assertTrue($app->page('data')->isCacheable());
		$this->assertTrue($app->page('data')->isCacheable(VersionId::latest()));
		$this->assertFalse($app->page('data')->isCacheable(VersionId::changes()));
	}

	public function testIsCacheableDisabledCache(): void
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

	public function testRenderCache(): void
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('default');

		$this->assertNull($cache->retrieve('default.latest.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$value = $cache->retrieve('default.latest.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());

		$html2 = $page->render();
		$this->assertSame($html1, $html2);
	}

	public function testRenderCacheCustomExpiry(): void
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('expiry');

		$this->assertNull($cache->retrieve('expiry.latest.html'));

		$time = $page->render();

		$value = $cache->retrieve('expiry.latest.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($time, $value->value()['html']);
		$this->assertSame((int)$time, $value->expires());
	}

	public function testRenderCacheMetadata(): void
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('metadata');

		$this->assertNull($cache->retrieve('metadata.latest.html'));

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

	public function testRenderCacheDisabled(): void
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('disabled');

		$this->assertNull($cache->retrieve('disabled.latest.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$this->assertNull($cache->retrieve('disabled.latest.html'));

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

	#[DataProvider('dynamicProvider')]
	public function testRenderCacheDynamicNonActive(
		string $slug,
		array $dynamicElements
	): void {
		$cache = $this->app->cache('pages');
		$page  = $this->app->page($slug);

		$this->assertNull($cache->retrieve($slug . '.latest.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$cacheValue = $cache->retrieve($slug . '.latest.html');
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

	#[DataProvider('dynamicProvider')]
	public function testRenderCacheDynamicActiveOnFirstRender(
		string $slug,
		array $dynamicElements
	): void {
		$_COOKIE['foo'] = $_COOKIE['kirby_session'] = 'bar';
		$this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Bearer brown-bearer'
			]
		]);

		$cache = $this->app->cache('pages');
		$page  = $this->app->page($slug);

		$this->assertNull($cache->retrieve($slug . '.latest.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$cacheValue = $cache->retrieve($slug . '.latest.html');
		$this->assertNull($cacheValue);

		// reset the Kirby Responder object
		$this->setUp();
		$html2 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html2);
		$this->assertNotSame($html1, $html2);
	}

	#[DataProvider('dynamicProvider')]
	public function testRenderCacheDynamicActiveOnSecondRender(
		string $slug,
		array $dynamicElements
	): void {
		$cache = $this->app->cache('pages');
		$page  = $this->app->page($slug);

		$this->assertNull($cache->retrieve($slug . '.latest.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$cacheValue = $cache->retrieve($slug . '.latest.html');
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

	public function testRenderCacheDataInitial(): void
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('data');

		$this->assertNull($cache->retrieve('data.latest.html'));

		$html = $page->render(['test' => 'custom test']);
		$this->assertStringStartsWith('This is a custom test:', $html);

		$this->assertNull($cache->retrieve('data.latest.html'));
	}

	public function testRenderCacheDataPreCached(): void
	{
		$cache = $this->app->cache('pages');
		$page  = $this->app->page('data');

		$this->assertNull($cache->retrieve('data.latest.html'));

		$html1 = $page->render();
		$this->assertStringStartsWith('This is a test:', $html1);

		$value = $cache->retrieve('data.latest.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());

		$html2 = $page->render(['test' => 'custom test']);
		$this->assertStringStartsWith('This is a custom test:', $html2);

		// cache still stores the non-custom result
		$value = $cache->retrieve('data.latest.html');
		$this->assertInstanceOf(Value::class, $value);
		$this->assertSame($html1, $value->value()['html']);
		$this->assertNull($value->expires());
	}

	public function testRenderRepresentationDefault(): void
	{
		$page = $this->app->page('representation');

		$this->assertSame('<html>Some HTML: representation</html>', $page->render());
	}

	public function testRenderRepresentationOverride(): void
	{
		$page = $this->app->page('representation');

		$this->assertSame('<html>Some HTML: representation</html>', $page->render(contentType: 'html'));
		$this->assertSame('{"some json": "representation"}', $page->render(contentType: 'json'));
	}

	public function testRenderRepresentationMissing(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The content representation cannot be found');

		$page = $this->app->page('representation');
		$page->render(contentType: 'txt');
	}

	public function testRenderTemplateMissing(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The default template does not exist');

		$page = $this->app->page('invalid');
		$page->render();
	}

	public function testRenderController(): void
	{
		$page = $this->app->page('controller');

		$this->assertSame('Data says TEST: controller and default!', $page->render());
		$this->assertSame('Data says TEST: controller and custom!', $page->render(['test' => 'override', 'test2' => 'custom']));
	}

	public function testRenderHookBefore(): void
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

	public function testRenderHookAfter(): void
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

	public function testRenderVersionDetectedFromRequest(): void
	{
		$page = $this->app->page('version');
		$page->version('latest')->save(['title' => 'Latest Title']);
		$page->version('changes')->save(['title' => 'Changes Title']);

		$this->assertSame("Version: latest\nContent: Latest Title", $page->render());

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('changes')->previewToken(),
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame("Version: changes\nContent: Changes Title", $page->render());
	}

	public function testRenderVersionDetectedFromRequestDraft(): void
	{
		$page = $this->app->page('version-draft');
		$page->version('latest')->save(['title' => 'Latest Title']);
		$page->version('changes')->save(['title' => 'Changes Title']);

		// manual renders of drafts falls back to the latest version even if
		// the draft couldn't be rendered "publicly" by `$kirby->resolve()`
		$this->assertSame("Version: latest\nContent: Latest Title", $page->render());

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('changes')->previewToken(),
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame("Version: changes\nContent: Changes Title", $page->render());
	}

	public function testRenderVersionDetectedRecursive(): void
	{
		$versionPage = $this->app->page('version');
		$versionPage->version('latest')->save(['title' => 'Latest Title']);
		$versionPage->version('changes')->save(['title' => 'Changes Title']);

		$page = $this->app->page('version-recursive');

		$this->assertSame("<recursive>\nVersion: latest\nContent: Latest Title\n</recursive>", $page->render());
		$this->assertSame("<recursive>\nVersion: latest\nContent: Latest Title\n</recursive>", $page->render(versionId: 'latest'));

		// the overridden version propagates to the lower level
		$this->assertSame("<recursive>\nVersion: changes\nContent: Changes Title\n</recursive>", $page->render(versionId: 'changes'));

		// even if the request says something else
		$this->app = $this->app->clone([
			'request' => [
				'query' => ['_version' => 'changes']
			]
		]);

		$this->assertSame("<recursive>\nVersion: latest\nContent: Latest Title\n</recursive>", $page->render(versionId: 'latest'));
	}

	public function testRenderVersionManual(): void
	{
		$page = $this->app->page('version');
		$page->version('latest')->save(['title' => 'Latest Title']);
		$page->version('changes')->save(['title' => 'Changes Title']);

		$this->assertSame("Version: latest\nContent: Latest Title", $page->render(versionId: 'latest'));
		$this->assertSame("Version: latest\nContent: Latest Title", $page->render(versionId: VersionId::latest()));
		$this->assertSame("Version: changes\nContent: Changes Title", $page->render(versionId: 'changes'));
		$this->assertSame("Version: changes\nContent: Changes Title", $page->render(versionId: VersionId::changes()));

		$this->assertNull(VersionId::$render);
	}

	public function testRenderVersionException(): void
	{
		$page = $this->app->page('version-exception');

		try {
			$page->render(versionId: 'changes');
		} catch (Exception) {
			// exception itself is not relevant for this test
		}

		// global state always needs to be reset after rendering
		$this->assertNull(VersionId::$render);
	}

	public function testRenderVersionFromRequestAuthenticated(): void
	{
		$page = $this->app->page('default');

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('latest')->previewToken(),
					'_version' => 'latest'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('changes')->previewToken(),
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame('changes', $page->renderVersionFromRequest()->value());
	}

	public function testRenderVersionFromRequestAuthenticatedDraft(): void
	{
		$page = $this->app->page('version-draft');

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('latest')->previewToken(),
					'_version' => 'latest'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('changes')->previewToken(),
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame('changes', $page->renderVersionFromRequest()->value());
	}

	public function testRenderVersionFromRequestMismatch(): void
	{
		$page = $this->app->page('default');

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('changes')->previewToken(),
					'_version' => 'latest'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('latest')->previewToken(),
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());
	}

	public function testRenderVersionFromRequestInvalidId(): void
	{
		$page       = $this->app->page('default');
		$draft      = $this->app->page('version-draft');
		$draftChild = $this->app->page('version-draft/a-child');

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => $page->version('changes')->previewToken(),
					'_version' => 'some-gibberish'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());
		$this->assertNull($draft->renderVersionFromRequest());
		$this->assertNull($draftChild->renderVersionFromRequest());
	}

	public function testRenderVersionFromRequestMissingId(): void
	{
		$page = $this->app->page('default');

		$this->app->clone([
			'request' => [
				'query' => [
					'_token' => $page->version('changes')->previewToken()
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());
	}

	public function testRenderVersionFromRequestMissingToken(): void
	{
		$page       = $this->app->page('default');
		$draft      = $this->app->page('version-draft');
		$draftChild = $this->app->page('version-draft/a-child');

		$this->app->clone([
			'request' => [
				'query' => [
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());
		$this->assertNull($draft->renderVersionFromRequest());
		$this->assertNull($draftChild->renderVersionFromRequest());

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => '',
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());
		$this->assertNull($draft->renderVersionFromRequest());
		$this->assertNull($draftChild->renderVersionFromRequest());
	}

	public function testRenderVersionFromRequestInvalidToken(): void
	{
		$page       = $this->app->page('default');
		$draft      = $this->app->page('version-draft');
		$draftChild = $this->app->page('version-draft/a-child');

		$this->app->clone([
			'request' => [
				'query' => [
					'_token'   => 'some-gibberish',
					'_version' => 'changes'
				]
			]
		]);

		$this->assertSame('latest', $page->renderVersionFromRequest()->value());
		$this->assertNull($draft->renderVersionFromRequest());
		$this->assertNull($draftChild->renderVersionFromRequest());
	}
}
