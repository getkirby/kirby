<?php

namespace Kirby\Panel;

use Kirby\Cms\Language;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(State::class)]
class StateTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.State';

	public function testConfig(): void
	{
		// without custom data
		$state = new State();
		$config  = $state->config();
		$config = A::apply($config);

		$this->assertArrayHasKey('api', $config);
		$this->assertArrayHasKey('debug', $config);
		$this->assertArrayHasKey('kirbytext', $config);
		$this->assertArrayHasKey('translation', $config);
		$this->assertArrayHasKey('upload', $config);
	}

	public function testData(): void
	{
		// without custom data
		$state = new State();
		$data  = $state->data();

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

	public function testDataWithCustomViewData(): void
	{
		$state = new State([
			'props' => $props = [
				'foo' => 'bar'
			]
		]);
		$data = $state->data();
		$data = A::apply($data);

		$this->assertSame($props, $data['view']['props']);
	}

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
		$state = new State();
		$this->assertSame('rtl', $state->direction());
	}

	public function testFilter()
	{
		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		// default (no special request)
		$result = (new State())->filter($data);

		$this->assertSame($data, $result);
	}

	public function testFilterOnlyRequest(): void
	{
		// empty only
		$data   = ['foo' => 'bar'];
		$result = (new State())->filter($data);
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

		$result = (new State())->filter($data);
		$this->assertSame(['a' => 'A'], $result);

		// via headers
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Panel-Only' => 'a',
				]
			]
		]);

		$data = [
			'a' => 'A',
			'b' => 'B'
		];

		$result = (new State())->filter($data);
		$this->assertSame(['a' => 'A'], $result);
	}

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

		$result = (new State())->filter($data);

		$expected = [
			'a' => 'A',
			'urls' => [
				'api' => '/api',
				'site' => '/'
			]
		];

		$this->assertSame($expected, $result);
	}

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

		$result = (new State())->filter($data);

		$expected = [
			'b' => [
				'c' => 'C'
			]
		];

		$this->assertSame($expected, $result);
	}

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

		$result = (new State())->filter($data);

		$expected = [
			'a' => 'A',
			'urls' => [
				'site' => '/'
			]
		];

		$this->assertSame($expected, $result);
	}

	public function testFilterGlobalsRequest(): void
	{
		// not included
		$data = (new State())->filter([]);
		$this->assertArrayNotHasKey('translation', $data);

		// empty globals
		$data = ['foo' => 'bar'];
		$result = (new State())->filter($data);
		$this->assertSame($data, $result);

		// via query
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_globals' => 'translation'
				]
			]
		]);

		$data = (new State())->filter([]);
		$this->assertArrayHasKey('translation', $data);

		// via header
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Panel-Globals' => 'translation'
				]
			]
		]);

		$data = (new State())->filter([]);
		$this->assertArrayHasKey('translation', $data);
	}

	public function testGlobals(): void
	{
		// defaults
		$state   = new State();
		$globals = $state->globals();

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
		$state   = new State();
		$globals = $state->globals();
		$globals = A::apply($globals);
		$this->assertSame('de', $globals['translation']['code']);
	}

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

		$state = new State();

		// multilang
		$multilang = $state->multilang();
		$this->assertTrue($multilang);

		// languages
		$languages = $state->languages();
		$language  = $state->language();
		$direction = $state->direction();
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

		$areas = new Areas([
			[
				'id' => 'a',
				'searches' => [
					'foo' => [],
				]
			],
			[
				'id' => 'b',
				'searches' => [
					'bar' => [],
				]
			],
			[
				'id' => 'c',
				'searches' => [
					'test' => [],
				]
			]
		]);

		$state    = new State(areas: $areas);
		$searches = $state->searches();

		$this->assertArrayHasKey('foo', $searches);
		$this->assertArrayNotHasKey('bar', $searches);
		$this->assertArrayHasKey('test', $searches);
	}

	public function testSystem(): void
	{
		// without custom data
		$state   = new State();
		$system  = $state->system();
		$system = A::apply($system);

		$this->assertArrayHasKey('ascii', $system);
		$this->assertArrayHasKey('csrf', $system);
		$this->assertArrayHasKey('isLocal', $system);
		$this->assertArrayHasKey('locales', $system);
		$this->assertArrayHasKey('slugs', $system);
		$this->assertArrayHasKey('title', $system);
	}

	public function testToArray(): void
	{
		$state = new State();
		$array = $state->toArray(globals: false);
		$this->assertArrayHasKey('user', $array);
		$this->assertArrayNotHasKey('config', $array);

		$array = $state->toArray(globals: true);
		$this->assertArrayHasKey('user', $array);
		$this->assertArrayHasKey('config', $array);
	}

	public function testTranslation(): void
	{
		$state       = new State();
		$translation = $state->translation();
		$translation = A::apply($translation);

		$this->assertSame('en', $translation['code']);
		$this->assertArrayHasKey('data', $translation);
		$this->assertSame('ltr', $translation['direction']);
		$this->assertSame('English', $translation['name']);
		$this->assertSame(0, $translation['weekday']);
	}

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

		$state       = new State();
		$translation = $state->translation();
		$translation = A::apply($translation);

		$this->assertSame('de', $translation['code']);
		$this->assertArrayHasKey('data', $translation);
		$this->assertSame('ltr', $translation['direction']);
		$this->assertSame('Deutsch', $translation['name']);
		$this->assertSame(1, $translation['weekday']);
	}

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
		$state = new State();
		$url   = $state->url();
		$this->assertSame('https://localhost.com:8888/foo/bar?foo=bar', $url);
	}

	public function testUrls(): void
	{
		$state = new State();
		$urls  = $state->urls();
		$this->assertArrayHasKey('api', $urls);
		$this->assertArrayHasKey('site', $urls);
	}

	public function testUserAuthenticated(): void
	{
		// authenticate pseudo user
		$this->app->impersonate('kirby');

		$state = new State();

		// user
		$user = $state->user();
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
		$permissions = $state->permissions();
		$permissions = A::apply($permissions);

		$this->assertSame(
			$this->app->user()->role()->permissions()->toArray(),
			$permissions
		);
	}
}
