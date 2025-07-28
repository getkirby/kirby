<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\TestCase;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Panel\View
 */
class ViewTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.View';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
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

	/**
	 * @covers ::apply
	 */
	public function testApply()
	{
		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		// default (no special request)
		$result = View::apply($data);

		$this->assertSame($data, $result);
	}

	/**
	 * @covers ::apply
	 * @covers ::applyGlobals
	 */
	public function testApplyGlobals(): void
	{
		// not included
		$data = View::apply([]);
		$this->assertArrayNotHasKey('$translation', $data);

		// via query
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_globals' => '$translation'
				]
			]
		]);

		$data = View::apply([]);
		$this->assertArrayHasKey('$translation', $data);

		// via header
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Fiber-Globals' => '$translation'
				]
			]
		]);

		$data = View::apply([]);
		$this->assertArrayHasKey('$translation', $data);

		// empty globals
		$data = ['foo' => 'bar'];
		$this->assertSame($data, View::applyGlobals($data, ''));
	}

	/**
	 * @covers ::apply
	 * @covers ::applyOnly
	 */
	public function testApplyOnly(): void
	{
		// via get
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_only' => 'a',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = View::apply($data);

		$this->assertSame(['a' => 'A'], $result);

		// via headers
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Fiber-Only' => 'a',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = View::apply($data);

		$this->assertSame(['a' => 'A'], $result);

		// empty only
		$data = ['foo' => 'bar'];
		$this->assertSame($data, View::applyOnly($data, ''));
	}

	/**
	 * @covers ::apply
	 * @covers ::applyOnly
	 */
	public function testApplyOnlyWithGlobal(): void
	{
		// simulate a simple partial request
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_only' => 'a,$urls',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = View::apply($data);

		$expected = [
			'a' => 'A',
			'$urls' => [
				'api' => '/api',
				'site' => '/'
			]
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::apply
	 * @covers ::applyOnly
	 */
	public function testApplyOnlyWithNestedData(): void
	{
		// simulate a simple partial request
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_only' => 'b.c',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => [
				'c' => 'C'
			]
		];

		$result = View::apply($data);

		$expected = [
			'b' => [
				'c' => 'C'
			]
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::apply
	 * @covers ::applyOnly
	 */
	public function testApplyOnlyWithNestedGlobal(): void
	{
		// simulate a simple partial request
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_only' => 'a,$urls.site',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = View::apply($data);

		$expected = [
			'a' => 'A',
			'$urls' => [
				'site' => '/'
			]
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::data
	 */
	public function testData(): void
	{
		// without custom data
		$data = View::data();

		$this->assertInstanceOf('Closure', $data['$menu']);
		$this->assertInstanceOf('Closure', $data['$direction']);
		$this->assertInstanceOf('Closure', $data['$language']);
		$this->assertInstanceOf('Closure', $data['$languages']);
		$this->assertSame([], $data['$permissions']);
		$this->assertSame('missing', $data['$license']);
		$this->assertFalse($data['$multilang']);
		$this->assertSame('/', $data['$url']);
		$this->assertInstanceOf('Closure', $data['$user']);
		$this->assertInstanceOf('Closure', $data['$view']);

		// default view settings
		$view = A::apply($data)['$view'];

		$this->assertSame([], $view['breadcrumb']);
		$this->assertSame(200, $view['code']);
		$this->assertSame('', $view['path']);
		$this->assertIsInt($view['timestamp']);
		$this->assertSame([], $view['props']);
		$this->assertSame('pages', $view['search']);

		$this->assertArrayNotHasKey('views', $view);
		$this->assertArrayNotHasKey('dialogs', $view);
	}

	/**
	 * @covers ::data
	 */
	public function testDataWithCustomRequestUrl(): void
	{
		$this->app = $this->app->clone([
			'cli' => false,
			'options' => [
				'url' => 'https://localhost.com:8888'
			],
			'server' => [
				'REQUEST_URI' => '/foo/bar/?foo=bar'
			]
		]);

		// without custom data
		$data = View::data();

		$this->assertSame('https://localhost.com:8888/foo/bar?foo=bar', $data['$url']);
	}

	/**
	 * @covers ::data
	 */
	public function testDataWithCustomProps(): void
	{
		$data = View::data([
			'props' => $props = [
				'foo' => 'bar'
			]
		]);

		$data = A::apply($data);

		$this->assertSame($props, $data['$view']['props']);
	}

	/**
	 * @covers ::data
	 */
	public function testDataWithLanguages(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'en', 'name' => 'English', 'default' => true],
				['code' => 'de', 'name' => 'Deutsch']
			],
			'options' => [
				'languages' => true
			]
		]);

		// without custom data
		$data = View::data();

		// resolve lazy data
		$data = A::apply($data);

		$this->assertTrue($data['$multilang']);

		$expected = [
			[
				'code'      => 'en',
				'default'   => true,
				'direction' => 'ltr',
				'locale'    => [LC_ALL => 'en'],
				'name'      => 'English',
				'rules'     => Language::loadRules('en'),
				'url'       => '/en'
			],
			[
				'code'      => 'de',
				'default'   => false,
				'direction' => 'ltr',
				'locale'    => [LC_ALL => 'de'],
				'name'      => 'Deutsch',
				'rules'     => Language::loadRules('de'),
				'url'       => '/de'
			]
		];

		$this->assertSame($expected, $data['$languages']);
		$this->assertSame($expected[0], $data['$language']);
		$this->assertNull($data['$direction']);
	}

	/**
	 * @covers ::data
	 */
	public function testDataWithDirection(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				['code' => 'en', 'name' => 'English', 'default' => true],
				['code' => 'de', 'name' => 'Deutsch'],
				['code' => 'ar', 'name' => 'Arabic', 'direction' => 'rtl'],
			],
			'options' => [
				'languages' => true
			]
		]);

		// set non-default, non-user language
		$this->app->setCurrentLanguage('ar');

		// authenticate pseudo user
		$this->app->impersonate('kirby');

		// without custom data
		$data = View::data();

		// resolve lazy data
		$data = A::apply($data);


		$this->assertSame('rtl', $data['$direction']);
	}

	/**
	 * @covers ::data
	 */
	public function testDataWithAuthenticatedUser(): void
	{
		// authenticate pseudo user
		$this->app->impersonate('kirby');

		// without custom data
		$data = View::data();

		// resolve lazy data
		$data = A::apply($data);

		// user
		$expected = [
			'email'    => 'kirby@getkirby.com',
			'id'       => 'kirby',
			'language' => 'en',
			'role'     => 'admin',
			'username' => 'kirby@getkirby.com'
		];

		$this->assertSame($expected, $data['$user']);
		$this->assertSame($this->app->user()->role()->permissions()->toArray(), $data['$permissions']);
	}

	/**
	 * @covers ::error
	 */
	public function testError(): void
	{
		// without user
		$error = View::error('Test');

		$expected = [
			'code' => 404,
			'component' => 'k-error-view',
			'error' => 'Test',
			'props' => [
				'error' => 'Test',
				'layout' => 'outside'
			],
			'title' => 'Error'
		];

		$this->assertSame($expected, $error);

		// with user
		$this->app->impersonate('kirby');
		$error = View::error('Test');

		$this->assertSame('inside', $error['props']['layout']);

		// user without panel access
		$this->app->impersonate('nobody');
		$error = View::error('Test');

		$this->assertSame('outside', $error['props']['layout']);
	}

	/**
	 * @covers ::error
	 */
	public function testErrorWithCustomCode(): void
	{
		$error = View::error('Test', 403);
		$this->assertSame(403, $error['code']);
	}

	/**
	 * @covers ::globals
	 */
	public function testGlobals(): void
	{
		// defaults
		$globals = View::globals();

		$this->assertInstanceOf('Closure', $globals['$config']);
		$this->assertInstanceOf('Closure', $globals['$system']);
		$this->assertInstanceOf('Closure', $globals['$system']);
		$this->assertInstanceOf('Closure', $globals['$translation']);
		$this->assertInstanceOf('Closure', $globals['$urls']);

		// defaults after apply
		$globals     = A::apply($globals);
		$config      = $globals['$config'];
		$system      = $globals['$system'];
		$translation = $globals['$translation'];
		$urls        = $globals['$urls'];

		// $config
		$this->assertFalse($config['debug']);
		$this->assertTrue($config['kirbytext']);
		$this->assertSame('en', $config['translation']);

		// $system
		$this->assertSame(Str::$ascii, $system['ascii']);
		$this->assertSame(csrf(), $system['csrf']);
		$this->assertFalse($system['isLocal']);
		$this->assertArrayHasKey('de', $system['locales']);
		$this->assertArrayHasKey('en', $system['locales']);
		$this->assertSame('en_US', $system['locales']['en']);
		$this->assertSame('de_DE', $system['locales']['de']);

		// $translation
		$this->assertSame('en', $translation['code']);
		$this->assertSame($this->app->translation('en')->dataWithFallback(), $translation['data']);
		$this->assertSame('ltr', $translation['direction']);
		$this->assertSame('English', $translation['name']);

		// $urls
		$this->assertSame('/api', $urls['api']);
		$this->assertSame('/', $urls['site']);
	}

	/**
	 * @covers ::globals
	 */
	public function testGlobalsWithUser(): void
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
		$globals = View::globals();
		$globals = A::apply($globals);
		$this->assertSame('de', $globals['$translation']['code']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponseAsHTML(): void
	{
		// create panel dist files first to avoid redirect
		(new Assets())->link();

		// get panel response
		$response = View::response([
			'test' => 'Test'
		]);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(200, $response->code());
		$this->assertSame('text/html', $response->type());
		$this->assertSame('UTF-8', $response->charset());
		$this->assertNotNull($response->body());
	}

	/**
	 * @covers ::response
	 */
	public function testResponseAsJSON(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true
				]
			]
		]);

		// get panel response
		$response = View::response([
			'test' => 'Test'
		]);

		$this->assertSame('application/json', $response->type());
		$this->assertSame('true', $response->header('X-Fiber'));
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromRedirect()
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$redirect = new \Kirby\Panel\Redirect('https://getkirby.com');
		$response = View::response($redirect);

		$this->assertInstanceOf(Response::class, $response);

		$this->assertSame(302, $response->code());
		$this->assertSame('https://getkirby.com', $response->header('Location'));
	}

	public function testResponseFromRedirectRefresh(): void
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$redirect = new Redirect('https://getkirby.com', refresh: 5);
		$response = View::response($redirect);

		$this->assertInstanceOf(Response::class, $response);

		$this->assertSame(302, $response->code());
		$this->assertSame('5; url=https://getkirby.com', $response->header('Refresh'));
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromKirbyException()
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$exception = new NotFoundException(message: 'Test');
		$response  = View::response($exception);
		$json      = json_decode($response->body(), true);

		$this->assertSame(404, $response->code());
		$this->assertSame('k-error-view', $json['$view']['component']);
		$this->assertSame('Test', $json['$view']['props']['error']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromException()
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$exception = new \Exception('Test');
		$response  = View::response($exception);
		$json      = json_decode($response->body(), true);

		$this->assertSame(500, $response->code());
		$this->assertSame('k-error-view', $json['$view']['component']);
		$this->assertSame('Test', $json['$view']['props']['error']);
	}

	/**
	 * @covers ::response
	 */
	public function testResponseFromUnsupportedResult()
	{
		// fake json request for easier assertions
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true,
				]
			]
		]);

		$response = View::response(1234);
		$json     = json_decode($response->body(), true);

		$this->assertSame(500, $response->code());
		$this->assertSame('k-error-view', $json['$view']['component']);
		$this->assertSame('Invalid Panel response', $json['$view']['props']['error']);
	}

	/**
	 * @covers ::searches
	 */
	public function testSearches()
	{
		$areas  = [
			'a' => [
				'searches' => [
					'foo' => [],
				]
			],
			'b' => [
				'searches' => [
					'bar' => [],
				]
			],
			'c' => [
				'searches' => [
					'test' => [],
				]
			]
		];

		$permissions = [
			'access' => [
				'a' => true,
				'b' => false
			]
		];

		$searches = View::searches($areas, $permissions);

		$this->assertArrayHasKey('foo', $searches);
		$this->assertArrayNotHasKey('bar', $searches);
		$this->assertArrayHasKey('test', $searches);
	}
}
