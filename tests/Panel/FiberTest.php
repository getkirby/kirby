<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Panel\Fiber
 * @covers ::__construct
 */
class FiberTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Fiber';

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
	}

	/**
	 * @covers ::config
	 */
	public function testConfig(): void
	{
		// without custom data
		$fiber = new Fiber();
		$config  = $fiber->config();
		$config = A::apply($config);

		$this->assertArrayHasKey('api', $config);
		$this->assertArrayHasKey('debug', $config);
		$this->assertArrayHasKey('kirbytext', $config);
		$this->assertArrayHasKey('translation', $config);
		$this->assertArrayHasKey('upload', $config);
	}

	/**
	 * @covers ::data
	 */
	public function testData(): void
	{
		// without custom data
		$fiber = new Fiber();
		$data  = $fiber->data();

		$this->assertArrayHasKey('direction', $data);
		$this->assertArrayHasKey('language', $data);
		$this->assertArrayHasKey('languages', $data);
		$this->assertArrayHasKey('license', $data);
		$this->assertArrayHasKey('menu', $data);
		$this->assertArrayHasKey('multilang', $data);
		$this->assertArrayHasKey('permissions', $data);
		$this->assertArrayHasKey('url', $data);
		$this->assertArrayHasKey('user', $data);
		$this->assertArrayHasKey('view', $data);

		// default view settings
		$view = A::apply($data)['view'];

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
	public function testDataWithCustomViewData(): void
	{
		$fiber = new Fiber([
			'props' => $props = [
				'foo' => 'bar'
			]
		]);
		$data = $fiber->data();
		$data = A::apply($data);

		$this->assertSame($props, $data['view']['props']);
	}

	/**
	 * @covers ::direction
	 */
	public function testDirection(): void
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
		$fiber = new Fiber();
		$this->assertSame('rtl', $fiber->direction());
	}

	/**
	 * @covers ::filter
	 */
	public function testFilter()
	{
		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		// default (no special request)
		$result = (new Fiber())->filter($data);

		$this->assertSame($data, $result);
	}

	/**
	 * @covers ::filter
	 */
	public function testFilterOnlyRequest(): void
	{
		// empty only
		$data   = ['foo' => 'bar'];
		$result = (new Fiber())->filter($data);
		$this->assertSame($data, $result);

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

		$result = (new Fiber())->filter($data);
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

		$result = (new Fiber())->filter($data);
		$this->assertSame(['a' => 'A'], $result);
	}

	/**
	 * @covers ::filter
	 */
	public function testFilterOnlyRequestWithGlobal(): void
	{
		// simulate a simple partial request
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_only' => 'a,urls',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = (new Fiber())->filter($data);

		$expected = [
			'a' => 'A',
			'urls' => [
				'api' => '/api',
				'site' => '/'
			]
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::filter
	 */
	public function testFilterOnlyRequestWithNestedData(): void
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

		$result = (new Fiber())->filter($data);

		$expected = [
			'b' => [
				'c' => 'C'
			]
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::filter
	 */
	public function testFilterOnlyRequestWithNestedGlobal(): void
	{
		// simulate a simple partial request
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_only' => 'a,urls.site',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = (new Fiber())->filter($data);

		$expected = [
			'a' => 'A',
			'urls' => [
				'site' => '/'
			]
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::filter
	 */
	public function testFilterGlobalsRequest(): void
	{
		// not included
		$data = (new Fiber())->filter([]);
		$this->assertArrayNotHasKey('translation', $data);

		// empty globals
		$data = ['foo' => 'bar'];
		$result = (new Fiber())->filter($data);
		$this->assertSame($data, $result);

		// via query
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_globals' => 'translation'
				]
			]
		]);

		$data = (new Fiber())->filter([]);
		$this->assertArrayHasKey('translation', $data);

		// via header
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Fiber-Globals' => 'translation'
				]
			]
		]);

		$data = (new Fiber())->filter([]);
		$this->assertArrayHasKey('translation', $data);
	}

	/**
	 * @covers ::globals
	 */
	public function testGlobals(): void
	{
		// defaults
		$fiber   = new Fiber();
		$globals = $fiber->globals();

		$this->assertInstanceOf('Closure', $globals['config']);
		$this->assertInstanceOf('Closure', $globals['system']);
		$this->assertInstanceOf('Closure', $globals['system']);
		$this->assertInstanceOf('Closure', $globals['translation']);
		$this->assertInstanceOf('Closure', $globals['urls']);

		// defaults after apply
		$globals     = A::apply($globals);
		$config      = $globals['config'];
		$system      = $globals['system'];
		$translation = $globals['translation'];
		$urls        = $globals['urls'];

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
					'email'    => 'test@getkirby.com',
					'language' => 'de',
					'role'     => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$fiber   = new Fiber();
		$globals = $fiber->globals();
		$globals = A::apply($globals);
		$this->assertSame('de', $globals['translation']['code']);
	}

	/**
	 * @covers ::direction
	 * @covers ::language
	 * @covers ::languages
	 * @covers ::multilang
	 */
	public function testLanguages(): void
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

		$fiber = new Fiber();

		// multilang
		$multilang = $fiber->multilang();
		$this->assertTrue($multilang);

		// languages
		$languages = $fiber->languages();
		$language  = $fiber->language();
		$direction = $fiber->direction();
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

		$this->assertSame($expected, $languages);
		$this->assertSame($expected[0], $language);
		$this->assertNull($direction);
	}

	/**
	 * @covers ::searches
	 */
	public function testSearches()
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'a' => true,
							'b' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$areas  = [
			new Area(
				id: 'a',
				searches: [
					'foo' => [],
				]
			),
			new Area(
				id: 'b',
				searches: [
					'bar' => [],
				]
			),
			new Area(
				id: 'c',
				searches: [
					'test' => [],
				]
			)
		];

		$fiber    = new Fiber(areas: $areas);
		$searches = $fiber->searches();

		$this->assertArrayHasKey('foo', $searches);
		$this->assertArrayNotHasKey('bar', $searches);
		$this->assertArrayHasKey('test', $searches);
	}

	/**
	 * @covers ::system
	 */
	public function testSystem(): void
	{
		// without custom data
		$fiber   = new Fiber();
		$system  = $fiber->system();
		$system = A::apply($system);

		$this->assertArrayHasKey('ascii', $system);
		$this->assertArrayHasKey('csrf', $system);
		$this->assertArrayHasKey('isLocal', $system);
		$this->assertArrayHasKey('locales', $system);
		$this->assertArrayHasKey('slugs', $system);
		$this->assertArrayHasKey('title', $system);
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray(): void
	{
		$fiber = new Fiber();
		$array = $fiber->toArray(globals: false);
		$this->assertArrayHasKey('user', $array);
		$this->assertArrayNotHasKey('config', $array);

		$array = $fiber->toArray(globals: true);
		$this->assertArrayHasKey('user', $array);
		$this->assertArrayHasKey('config', $array);
	}

	/**
	 * @covers ::translation
	 */
	public function testTranslation(): void
	{
		$fiber       = new Fiber();
		$translation = $fiber->translation();
		$translation = A::apply($translation);

		$this->assertSame('en', $translation['code']);
		$this->assertArrayHasKey('data', $translation);
		$this->assertSame('ltr', $translation['direction']);
		$this->assertSame('English', $translation['name']);
		$this->assertSame(0, $translation['weekday']);
	}

	/**
	 * @covers ::translation
	 */
	public function testTranslationWithUserLanguage(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'email'    => 'test@getkirby.com',
					'language' => 'de',
					'role'     => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$fiber       = new Fiber();
		$translation = $fiber->translation();
		$translation = A::apply($translation);

		$this->assertSame('de', $translation['code']);
		$this->assertArrayHasKey('data', $translation);
		$this->assertSame('ltr', $translation['direction']);
		$this->assertSame('Deutsch', $translation['name']);
		$this->assertSame(1, $translation['weekday']);
	}

	/**
	 * @covers ::url
	 */
	public function testUrl(): void
	{
		// custom request url
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
		$fiber = new Fiber();
		$url   = $fiber->url();
		$this->assertSame('https://localhost.com:8888/foo/bar?foo=bar', $url);
	}

	/**
	 * @covers ::urls
	 */
	public function testUrls(): void
	{
		$fiber = new Fiber();
		$urls  = $fiber->urls();
		$this->assertArrayHasKey('api', $urls);
		$this->assertArrayHasKey('site', $urls);
	}

	/**
	 * @covers ::permissions
	 * @covers ::user
	 */
	public function testUserAuthenticated(): void
	{
		// authenticate pseudo user
		$this->app->impersonate('kirby');

		$fiber = new Fiber();

		// user
		$user = $fiber->user();
		$user = A::apply($user);

		$expected = [
			'email'    => 'kirby@getkirby.com',
			'id'       => 'kirby',
			'language' => 'en',
			'role'     => 'admin',
			'username' => 'kirby@getkirby.com'
		];

		$this->assertSame($expected, $user);


		// permissions;
		$permissions = $fiber->permissions();
		$permissions = A::apply($permissions);

		$this->assertSame(
			$this->app->user()->role()->permissions()->toArray(),
			$permissions
		);
	}
}
