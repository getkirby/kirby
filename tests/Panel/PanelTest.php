<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Panel::class)]
class PanelTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Panel';

	public function setUp(): void
	{
		Blueprint::$loaded = [];

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
		// fix installation issues by creating directories
		Dir::make(static::TMP . '/content');
		Dir::make(static::TMP . '/media');
		Dir::make(static::TMP . '/site/accounts');
		Dir::make(static::TMP . '/site/sessions');

		// let's pretend we are on a supported server
		$_SERVER['SERVER_SOFTWARE'] = 'php';
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);
	}

	public function testArea(): void
	{
		// defaults
		$result = Panel::area('test', []);
		$expected = [
			'id' => 'test',
			'label' => 'test',
			'breadcrumb' => [],
			'breadcrumbLabel' => 'test',
			'title' => 'test',
			'menu' => false,
			'link' => 'test',
			'search' => null
		];

		$this->assertSame($expected, $result);
	}

	public function testAreas(): void
	{
		// unauthenticated / uninstalled
		$areas = Panel::areas();

		$this->assertArrayHasKey('installation', $areas);
		$this->assertCount(1, $areas);

		// create the first admin
		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		// unauthenticated / installed
		$areas = Panel::areas();

		$this->assertArrayHasKey('login', $areas);
		$this->assertArrayHasKey('logout', $areas);
		$this->assertCount(2, $areas);

		// simulate a logged in user
		$this->app->impersonate('test@getkirby.com');

		// authenticated
		$areas = Panel::areas();

		$this->assertArrayHasKey('search', $areas);
		$this->assertArrayHasKey('site', $areas);
		$this->assertArrayHasKey('system', $areas);
		$this->assertArrayHasKey('users', $areas);
		$this->assertArrayHasKey('account', $areas);
		$this->assertArrayHasKey('logout', $areas);
		$this->assertArrayHasKey('lab', $areas);
		$this->assertCount(7, $areas);

		// authenticated with plugins
		$app = $this->app->clone([
			'areas' => [
				'todos' => fn () => []
			]
		]);

		$app->impersonate('test@getkirby.com');

		$areas = Panel::areas();

		$this->assertArrayHasKey('todos', $areas);
		$this->assertCount(8, $areas);
	}

	public function testButtons(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$core = Panel::buttons();

		// add custom buttons
		$this->app = $this->app->clone([
			'areas' => [
				'test' => fn () => [
					'buttons' => [
						'a' => ['component' => 'test-a'],
						'b' => ['component' => 'test-b']
					]
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$withCustoms = Panel::buttons();

		$this->assertSame(2, count($withCustoms) - count($core));
		$this->assertSame(['component' => 'test-b'], array_pop($withCustoms));
		$this->assertSame(['component' => 'test-a'], array_pop($withCustoms));
	}

	public function testFirewallWithoutUser(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access the panel');

		// no user
		$this->assertFalse(Panel::hasAccess());
		Panel::firewall();
	}

	public function testFirewallWithoutAcceptedUser(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access the panel');

		// user without panel access
		$this->app->impersonate('nobody');

		$this->assertFalse(Panel::hasAccess($this->app->user()));
		Panel::firewall($this->app->user());
	}

	public function testFirewallWithAcceptedUser(): void
	{
		// accepted user
		$this->app->impersonate('kirby');

		// general access
		$result = Panel::firewall($this->app->user());
		$this->assertTrue($result);

		$result = Panel::hasAccess($this->app->user());
		$this->assertTrue($result);

		// area access
		$result = Panel::firewall($this->app->user(), 'site');
		$this->assertTrue($result);

		$result = Panel::hasAccess($this->app->user(), 'site');
		$this->assertTrue($result);
	}

	public function testFirewallAreaAccess(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
					'permissions' => [
						'access' => [
							'system' => false
						]
					]
				]
			]
		]);

		// accepted user
		$app->impersonate('test@getkirby.com');

		// general access
		$result = Panel::firewall($app->user());
		$this->assertTrue($result);

		$result = Panel::hasAccess($app->user());
		$this->assertTrue($result);

		// no defined area permissions means access
		$this->assertTrue(Panel::hasAccess($app->user(), 'foo'));
		Panel::firewall($app->user(), 'foo');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to access this part of the panel');

		// no area access
		$this->assertFalse(Panel::hasAccess($app->user(), 'system'));
		Panel::firewall($app->user(), 'system');
	}

	public function testGo(): void
	{
		$thrown = false;
		try {
			Panel::go('test');
		} catch (Redirect $r) {
			$thrown = true;
			$this->assertSame('/panel/test', $r->getMessage());
			$this->assertSame(302, $r->getCode());
			$this->assertFalse($r->refresh());
		}
		$this->assertTrue($thrown);
	}

	public function testGoRefresh(): void
	{
		$thrown = false;
		try {
			Panel::go('test', 302, 5);
		} catch (Redirect $r) {
			$thrown = true;
			$this->assertSame('/panel/test', $r->getMessage());
			$this->assertSame(302, $r->getCode());
			$this->assertSame(5, $r->refresh());
		}
		$this->assertTrue($thrown);
	}

	public function testGoWithCustomCode(): void
	{
		try {
			Panel::go('test', 301);
		} catch (Redirect $r) {
			$this->assertSame(301, $r->getCode());
		}
	}

	public function testGoWithCustomSlug(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'slug' => 'foo'
				]
			]
		]);

		try {
			Panel::go('test');
		} catch (Redirect $r) {
			$this->assertSame('/foo/test', $r->getMessage());
			$this->assertSame(302, $r->getCode());
		}
	}

	public function testIsFiberRequest(): void
	{
		// standard request
		$result = Panel::isFiberRequest($this->app->request());
		$this->assertFalse($result);

		// fiber request via get
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true
				]
			]
		]);

		$result = Panel::isFiberRequest($this->app->request());
		$this->assertTrue($result);

		// fiber request via header
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Fiber' => true
				]
			]
		]);

		$result = Panel::isFiberRequest($this->app->request());
		$this->assertTrue($result);

		// other request than GET
		$this->app = $this->app->clone([
			'request' => [
				'method' => 'POST'
			]
		]);

		$result = Panel::isFiberRequest($this->app->request());
		$this->assertFalse($result);
	}

	public function testJson(): void
	{
		$response = Panel::json($data = ['foo' => 'bar']);

		$this->assertSame('application/json', $response->type());
		$this->assertSame('true', $response->header('X-Fiber'));
		$this->assertSame($data, json_decode($response->body(), true));
	}

	public function testMultilang(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages' => true
			]
		]);

		$this->assertTrue(Panel::multilang());
	}

	public function testMultilangWithImplicitLanguageInstallation(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$this->assertTrue(Panel::multilang());
	}

	public function testMultilangDisabled(): void
	{
		$this->assertFalse(Panel::multilang());
	}

	public function testResponse(): void
	{
		$response = new Response('Test');

		// response objects should not be modified
		$this->assertSame($response, Panel::response($response));
	}

	public function testResponseFromNullOrFalse(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		// null is interpreted as 404
		$response = Panel::response(null);
		$json     = json_decode($response->body(), true);

		$this->assertSame(404, $response->code());
		$this->assertSame('k-error-view', $json['$view']['component']);
		$this->assertSame('The data could not be found', $json['$view']['props']['error']);

		// false is interpreted as 404
		$response = Panel::response(false);
		$json     = json_decode($response->body(), true);

		$this->assertSame(404, $response->code());
		$this->assertSame('k-error-view', $json['$view']['component']);
		$this->assertSame('The data could not be found', $json['$view']['props']['error']);
	}

	public function testResponseFromString(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		// strings are interpreted as errors
		$response = Panel::response('Test');
		$json     = json_decode($response->body(), true);

		$this->assertSame(500, $response->code());
		$this->assertSame('k-error-view', $json['$view']['component']);
		$this->assertSame('Test', $json['$view']['props']['error']);
	}

	public function testRouterWithDisabledPanel(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => false
			]
		]);

		$result = Panel::router('/');

		$this->assertNull($result);
	}

	public function testRoutes(): void
	{
		$routes = Panel::routes([]);

		$this->assertSame('browser', $routes[0]['pattern']);
		$this->assertSame(['/', 'installation', 'login'], $routes[1]['pattern']);
		$this->assertSame('(:all)', $routes[2]['pattern']);
		$this->assertSame('Could not find Panel view for route: foo', $routes[2]['action']('foo'));
	}


	public function testRoutesForDialogs(): void
	{
		$area = [
			'dialogs' => [
				'test' => [
					'load'   => $load   = function () {
					},
					'submit' => $submit = function () {
					},
				]
			]
		];

		$routes = Panel::routesForDialogs('test', $area);

		$expected = [
			[
				'pattern' => 'dialogs/test',
				'type'    => 'dialog',
				'area'    => 'test',
				'action'  => $load,
			],
			[
				'pattern' => 'dialogs/test',
				'type'    => 'dialog',
				'area'    => 'test',
				'method'  => 'POST',
				'action'  => $submit,
			]
		];

		$this->assertSame($expected, $routes);
	}

	public function testRoutesForDialogsWithoutHandlers(): void
	{
		$area = [
			'dialogs' => [
				'test' => []
			]
		];

		$routes = Panel::routesForDialogs('test', $area);

		$this->assertSame('The load handler is missing', $routes[0]['action']());
		$this->assertSame('The submit handler is missing', $routes[1]['action']());
	}

	public function testRoutesForDropdowns(): void
	{
		$area = [
			'dropdowns' => [
				'test' => [
					'pattern' => 'test',
					'action'  => $action = fn () => [
						[
							'text' => 'Test',
							'link' => '/test'
						]
					]
				]
			]
		];

		$routes = Panel::routesForDropdowns('test', $area);

		$expected = [
			[
				'pattern' => 'dropdowns/test',
				'type'    => 'dropdown',
				'area'    => 'test',
				'method'  => 'GET|POST',
				'action'  => $action,
			]
		];

		$this->assertSame($expected, $routes);
	}

	public function testRoutesForDropdownsWithOptions(): void
	{
		$area = [
			'dropdowns' => [
				'test' => [
					'pattern' => 'test',
					'options' => $action = fn () => [
						[
							'text' => 'Test',
							'link' => '/test'
						]
					]
				]
			]
		];

		$routes = Panel::routesForDropdowns('test', $area);

		$expected = [
			[
				'pattern' => 'dropdowns/test',
				'type'    => 'dropdown',
				'area'    => 'test',
				'method'  => 'GET|POST',
				'action'  => $action,
			]
		];

		$this->assertSame($expected, $routes);
	}

	public function testRoutesForDropdownsWithShortcut(): void
	{
		$area = [
			'dropdowns' => [
				'test' => $action = fn () => [
					[
						'text' => 'Test',
						'link' => '/test'
					]
				]
			]
		];

		$routes = Panel::routesForDropdowns('test', $area);

		$expected = [
			[
				'pattern' => 'dropdowns/test',
				'type'    => 'dropdown',
				'area'    => 'test',
				'method'  => 'GET|POST',
				'action'  => $action,
			]
		];

		$this->assertSame($expected, $routes);
	}

	public function testRoutesForViews(): void
	{
		$area = [
			'views' => [
				[
					'pattern' => 'test',
					'action'  => $callback = function () {
					}
				]
			]
		];

		$routes = Panel::routesForViews('test', $area);

		$expected = [
			[
				'pattern' => 'test',
				'action'  => $callback,
				'area'    => 'test',
				'type'    => 'view'
			]
		];

		$this->assertSame($expected, $routes);
	}

	public function testSetLanguageWithoutRequest(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages' => true,
			],
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		// set for the first time
		$language = Panel::setLanguage();

		$this->assertSame('en', $language);
		$this->assertSame('en', $this->app->language()->code());

		// language is not stored in the session yet
		$this->assertNull($this->app->session()->get('panel.language'));
	}

	public function testSetLanguage(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			],
			'options' => [
				'languages' => true,
			],
			'request' => [
				'query' => [
					'language' => 'de'
				]
			]
		]);

		// set for the first time
		$language = Panel::setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->language()->code());

		// language is now stored in the session after request query
		$this->assertSame('de', $this->app->session()->get('panel.language'));
	}

	public function testSetLanguageWithCustomDefault(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'de',
					'name' => 'Deutsch',
					'default' => true
				],
				[
					'code' => 'en',
					'name' => 'English',
				],
			],
			'options' => [
				'languages' => true,
			]
		]);

		// set for the first time
		$language = Panel::setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->language()->code());
	}

	public function testSetLanguageViaGet(): void
	{
		// switch via get request
		// needs to come first before the app is cloned
		$_GET['language'] = 'de';

		$this->app = $this->app->clone([
			'options' => [
				'languages' => true,
			],
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		// set for the first time
		$language = Panel::setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->session()->get('panel.language'));
		$this->assertSame('de', $this->app->language()->code());
	}

	public function testSetLanguageInSingleLanguageSite(): void
	{
		$language = Panel::setLanguage();

		$this->assertNull($language);
		$this->assertNull($this->app->language());
	}

	public function testSetTranslation(): void
	{
		$translation = Panel::setTranslation($this->app);

		$this->assertSame('en', $translation);
		$this->assertSame('en', $this->app->translation()->code());
	}

	public function testSetTranslationViaUser(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'language' => 'de',
					'role' => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$translation = Panel::setTranslation($this->app);

		$this->assertSame('de', $translation);
		$this->assertSame('de', $this->app->translation()->code());
	}
}
