<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Page;
use Kirby\Content\VersionId;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Redirect;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\View;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

class PageWithoutChangesPreview extends Page
{
	public function previewUrl(VersionId|string $versionId = 'latest'): string|null
	{
		if ($versionId === 'changes') {
			return null;
		}

		return parent::previewUrl($versionId);
	}
}

#[CoversClass(PreviewViewController::class)]
class PreviewViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.PreviewViewController';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testButtons(): void
	{
		// page
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(3, $buttons->render());

		// site
		$model      = $this->app->site();
		$controller = new PreviewViewController($model, 'changes');
		$buttons    = $controller->buttons();
		$this->assertCount(2, $buttons->render());
	}

	public function testFactory(): void
	{
		$controller = PreviewViewController::factory('pages/test', 'changes');
		$this->assertInstanceOf(PreviewViewController::class, $controller);
		$this->assertSame($this->app->page('test'), $controller->model);
		$this->assertSame('changes', $controller->mode);

		$controller = PreviewViewController::factory('site', 'changes');
		$this->assertInstanceOf(PreviewViewController::class, $controller);
		$this->assertSame($this->app->site(), $controller->model);
	}

	public function testFactoryNotAccessible(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The site is not accessible');
		PreviewViewController::factory('site', 'changes');
	}

	public function testId(): void
	{
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'changes');
		$this->assertSame('page.preview', $controller->id());
	}

	public function testLoad(): void
	{
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'changes');
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-preview-view', $view->component);
		$this->assertSame('test | Preview', $view->title);

		$props = $view->props();
		$token = $model->version('changes')->previewToken();
		$this->assertSame('/test?_token=' . $token . '&_version=changes&_preview=true', $props['src']['changes']);
	}

	public function testLoadInvalid(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The preview is not available');
		$site       = $this->app->site();
		$controller = new PreviewViewController($site, 'changes');
		$controller->load();
	}

	public function testRedirect(): void
	{
		$model      = $this->app->page('test');
		$controller = new PreviewViewController($model, 'latest');
		$redirect   = $controller->redirect('latest');
		$this->assertSame(null, $redirect);

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/notes/page:2?foo=bar&_preview=true'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($model, 'latest');
		$redirect   = $controller->redirect();
		$this->assertSame('/panel/pages/notes/preview/latest?_query=foo%3Dbar&_params=page%3A2', $redirect);
	}

	public function testRedirectApiUrl(): void
	{
		$routes    = [];
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'hooks' => [
				'route:before' => function (string $path) use (&$routes) {
					$routes[] = $path;
				}
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/api/pages/test'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$controller = new PreviewViewController($model, 'latest');

		try {
			$controller->redirect();
			$this->fail('Expected a redirect');
		} catch (Redirect $e) {
			$this->assertSame('https://getkirby.com/api/pages/test', $e->location());
		}

		// the router must not be called for Panel or API URLs
		$this->assertSame([], $routes);
	}

	public function testRedirectCustomPanelSlug(): void
	{
		$routes    = [];
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'hooks' => [
				'route:before' => function (string $path) use (&$routes) {
					$routes[] = $path;
				}
			],
			'options' => [
				'panel.slug' => 'admin'
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/admin/pages/test/preview/latest'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$controller = new PreviewViewController($model, 'latest');

		try {
			$controller->redirect();
			$this->fail('Expected a redirect');
		} catch (Redirect $e) {
			$this->assertSame('https://getkirby.com/admin/pages/test/preview/latest', $e->location());
		}

		// the router must not be called for Panel or API URLs
		$this->assertSame([], $routes);
	}

	public static function externalUrlProvider(): array
	{
		return [
			['https://evil.example/notes'],
			['//evil.example/notes'],
			['/\\evil.example/notes'],
			['https://getkirby.com@evil.example/notes'],
		];
	}

	#[DataProvider('externalUrlProvider')]
	public function testRedirectExternalUrl(string $url): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'view' => $url
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($this->app->page('test'), 'latest');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('External URLs are not allowed for Panel redirects');

		$controller->redirect();
	}

	public function testRedirectInSubfolder(): void
	{
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/demo/notes?_preview=true'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com/demo'
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($model, 'latest');
		$redirect   = $controller->redirect();
		$this->assertSame('https://getkirby.com/demo/panel/pages/notes/preview/latest', $redirect);
	}

	public function testRedirectInSubfolderToSite(): void
	{
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/demo'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com/demo'
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($model, 'latest');
		$redirect   = $controller->redirect();
		$this->assertSame('https://getkirby.com/demo/panel/pages/home/preview/latest', $redirect);
	}

	public function testRedirectNotAccessible(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/forbidden' => [
					'options' => [
						'access' => false
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'test'],
					[
						'slug'     => 'hidden-page',
						'template' => 'forbidden'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/hidden-page'
				],
			]
		]);

		$this->app->impersonate('editor');

		$controller = new PreviewViewController($this->app->page('test'), 'latest');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "hidden-page" cannot be found');

		$controller->redirect();
	}

	public function testRedirectOutsideSubfolder(): void
	{
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/notes'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com/demo'
			]
		]);

		$controller = new PreviewViewController($model, 'latest');

		$this->expectException(Redirect::class);
		$this->expectExceptionMessage('https://getkirby.com/notes');

		$controller->redirect();
	}

	public function testRedirectPanelUrl(): void
	{
		$routes    = [];
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'hooks' => [
				'route:before' => function (string $path) use (&$routes) {
					$routes[] = $path;
				}
			],
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/panel/pages/test/preview/latest'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$controller = new PreviewViewController($model, 'latest');

		try {
			$controller->redirect();
			$this->fail('Expected a redirect');
		} catch (Redirect $e) {
			$this->assertSame('https://getkirby.com/panel/pages/test/preview/latest', $e->location());
		}

		// the router must not be called for Panel or API URLs
		$this->assertSame([], $routes);
	}

	public function testRedirectWithRelativeIndexUrl(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'view' => 'https://getkirby.com/some-route'
				],
			],
			'server' => [
				'HTTP_HOST'   => 'getkirby.com',
				'SERVER_NAME' => 'getkirby.com'
			],
			'urls' => [
				'index' => '/'
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($this->app->page('test'), 'latest');

		$this->expectException(Redirect::class);
		$this->expectExceptionMessage('https://getkirby.com/some-route');

		$controller->redirect();
	}

	public function testSrc(): void
	{
		$model      = $this->app->page('test');
		$token      = $model->version('changes')->previewToken();
		$controller = new PreviewViewController($model, 'compare');
		$src        = $controller->src();

		$this->assertSame('/test?_preview=true', $src['latest']);
		$this->assertSame('/test?_token=' . $token . '&_version=changes&_preview=true', $src['changes']);
	}

	public function testSrcBrowser(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/notes/page:2?foo=bar&_preview=true'
				],
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->site()->createChild([
			'slug' => 'notes'
		]);

		$token      = $this->app->page('notes')->version('changes')->previewToken();
		$controller = new PreviewViewController($this->app->page('test'), 'changes');
		$src        = $controller->src();

		$this->assertSame('/notes/page:2?_token=' . $token . '&_version=changes&foo=bar&_preview=true', $src['changes']);
	}

	#[DataProvider('externalUrlProvider')]
	public function testSrcBrowserExternalUrl(string $url): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => $url
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($this->app->page('test'), 'changes');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('External URLs are not allowed for Panel redirects');

		$controller->src();
	}

	public function testSrcBrowserInSubfolder(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/demo/notes?_preview=true'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com/demo'
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->site()->createChild([
			'slug' => 'notes'
		]);

		$token      = $this->app->page('notes')->version('changes')->previewToken();
		$controller = new PreviewViewController($this->app->page('test'), 'changes');
		$src        = $controller->src();

		$this->assertSame('https://getkirby.com/demo/notes?_token=' . $token . '&_version=changes&_preview=true', $src['changes']);
	}

	public function testSrcBrowserKeepsLanguage(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/de/notes'
				],
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->site()->createChild([
			'slug' => 'notes'
		]);

		$this->app->setCurrentLanguage('en');
		I18n::$locale = 'fr';

		$controller = new PreviewViewController($this->app->page('test'), 'latest');
		$src        = $controller->src();

		$this->assertStringStartsWith('/de/notes?', $src['latest']);
		$this->assertSame('fr', I18n::$locale);
		$this->assertSame('en', $this->app->language()->code());
	}

	public function testSrcBrowserKeepsLocale(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/notes'
				],
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->site()->createChild([
			'slug' => 'notes'
		]);

		I18n::$locale = 'de';

		$controller = new PreviewViewController($this->app->page('test'), 'latest');
		$src        = $controller->src();

		$this->assertStringStartsWith('/notes?', $src['latest']);
		$this->assertSame('de', I18n::$locale);
	}

	public function testSrcBrowserNotAccessible(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/forbidden' => [
					'options' => [
						'access' => false
					]
				]
			],
			'site' => [
				'children' => [
					['slug' => 'test'],
					[
						'slug'     => 'hidden-page',
						'template' => 'forbidden'
					]
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['id' => 'editor', 'role' => 'editor']
			],
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/hidden-page'
				],
			]
		]);

		$this->app->impersonate('editor');

		$controller = new PreviewViewController($this->app->page('test'), 'changes');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "hidden-page" cannot be found');

		$controller->src();
	}

	public function testSrcBrowserOutsideSubfolder(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/notes'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com/demo'
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->site()->createChild([
			'slug' => 'notes'
		]);

		$controller = new PreviewViewController($this->app->page('test'), 'changes');

		$this->expectException(Redirect::class);
		$this->expectExceptionMessage('https://getkirby.com/notes');

		$controller->src();
	}

	public function testSrcBrowserPanelUrl(): void
	{
		$routes    = [];
		$model     = $this->app->page('test');
		$this->app = $this->app->clone([
			'hooks' => [
				'route:before' => function (string $path) use (&$routes) {
					$routes[] = $path;
				}
			],
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/panel/pages/test/preview/changes'
				],
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$controller = new PreviewViewController($model, 'changes');

		try {
			$controller->src();
			$this->fail('Expected a redirect');
		} catch (Redirect $e) {
			$this->assertSame('https://getkirby.com/panel/pages/test/preview/changes', $e->location());
		}

		// the router must not be called for Panel or API URLs
		$this->assertSame([], $routes);
	}

	public function testSrcBrowserWithRelativeIndexUrl(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'browser' => 'https://getkirby.com/some-route'
				],
			],
			'server' => [
				'HTTP_HOST'   => 'getkirby.com',
				'SERVER_NAME' => 'getkirby.com'
			],
			'urls' => [
				'index' => '/'
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new PreviewViewController($this->app->page('test'), 'changes');

		$this->expectException(Redirect::class);
		$this->expectExceptionMessage('https://getkirby.com/some-route');

		$controller->src();
	}

	public function testSrcRedirectParamsQuery(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_params' => 'foo:bar',
					'_query'  => 'foo=bar'
				]
			]
		]);

		$this->app->impersonate('kirby');
		$model      = $this->app->page('test');
		$token      = $model->version('changes')->previewToken();
		$controller = new PreviewViewController($model, 'compare');
		$src        = $controller->src();

		$this->assertSame('/test/foo:bar?_preview=true&foo=bar', $src['latest']);
		$this->assertSame('/test/foo:bar?_token=' . $token . '&_version=changes&_preview=true&foo=bar', $src['changes']);
	}

	public function testSrcWithoutChangesPreview(): void
	{
		$model      = new PageWithoutChangesPreview(['slug' => 'test']);
		$controller = new PreviewViewController($model, 'compare');

		$this->assertSame(['latest' => '/test?_preview=true'], $controller->src());
	}
}
