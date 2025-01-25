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
	 * @covers ::data
	 */
	public function testData(): void
	{
		// without custom data
		$fiber = new Fiber();
		$data  = $fiber->data();

		$this->assertInstanceOf('Closure', $data['menu']);
		$this->assertInstanceOf('Closure', $data['direction']);
		$this->assertInstanceOf('Closure', $data['language']);
		$this->assertInstanceOf('Closure', $data['languages']);
		$this->assertSame([], $data['permissions']);
		$this->assertInstanceOf('Closure', $data['license']);
		$this->assertSame('missing', $data['license']());
		$this->assertFalse($data['multilang']);
		$this->assertSame('/', $data['url']);
		$this->assertInstanceOf('Closure', $data['user']);
		$this->assertInstanceOf('Closure', $data['view']);

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
		$fiber = new Fiber();
		$data  = $fiber->data();

		$this->assertSame('https://localhost.com:8888/foo/bar?foo=bar', $data['url']);
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
		$fiber = new Fiber();
		$data  = $fiber->data();

		// resolve lazy data
		$data = A::apply($data);

		$this->assertTrue($data['multilang']);

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

		$this->assertSame($expected, $data['languages']);
		$this->assertSame($expected[0], $data['language']);
		$this->assertNull($data['direction']);
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
		$fiber = new Fiber();
		$data  = $fiber->data();

		// resolve lazy data
		$data = A::apply($data);


		$this->assertSame('rtl', $data['direction']);
	}

	/**
	 * @covers ::data
	 */
	public function testDataWithAuthenticatedUser(): void
	{
		// authenticate pseudo user
		$this->app->impersonate('kirby');

		// without custom data
		$fiber = new Fiber();
		$data  = $fiber->data();

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

		$this->assertSame($expected, $data['user']);
		$this->assertSame($this->app->user()->role()->permissions()->toArray(), $data['permissions']);
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
	public function testFilterOnlyrequestWithGlobal(): void
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

		$fiber    = new Fiber(options: ['areas' => $areas]);
		$searches = $fiber->searches();

		$this->assertArrayHasKey('foo', $searches);
		$this->assertArrayNotHasKey('bar', $searches);
		$this->assertArrayHasKey('test', $searches);
	}
}
