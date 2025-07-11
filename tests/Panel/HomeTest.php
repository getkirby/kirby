<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Uri;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @coversDefaultClass \Kirby\Panel\Home
 * @covers ::__construct
 */
class HomeTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Home';

	public function setUp(): void
	{
		Blueprint::$loaded = [];

		// fake a valid server
		$_SERVER['SERVER_SOFTWARE'] = 'php';

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'options' => [
				'panel' => [
					'install' => true
				]
			],
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		unset($_SERVER['SERVER_SOFTWARE']);
		Dir::remove(static::TMP);
	}

	public function testAlternative(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/site', $home->alternative());
	}

	public function testAlternativeWithLimitedAccess(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'site' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/users', $home->alternative());
	}

	public function testAlternativeWithOnlyAccessToAccounts(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'site'   => false,
							'users'  => false,
							'system' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/account', $home->alternative());
	}

	public function testAlternativeWithoutPanelAccess(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'*' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame('/', $home->alternative());
	}

	public function testAlternativeWithoutViewAccess(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'account'   => false,
							'languages' => false,
							'site'      => false,
							'system'    => false,
							'users'     => false,
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('There’s no available Panel page to redirect to');

		$this->app->panel()->home()->alternative();
	}

	public function testHasAccess(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();

		$this->assertTrue($home->hasAccess('site'));
		$this->assertTrue($home->hasAccess('pages/test'));
		$this->assertTrue($home->hasAccess('users/test@getkirby.com'));
		$this->assertTrue($home->hasAccess('account'));

		// dialogs and dropdowns never get access
		$this->assertFalse($home->hasAccess('dialogs/users/create'));
		$this->assertFalse($home->hasAccess('dropdowns/users/test@getkirby.com'));

		// invalid routes return false
		$this->assertFalse($home->hasAccess('does/not/exist/at/all'));

		// unauthenticated views return true
		$this->assertTrue($home->hasAccess('browser'));
	}

	public function testHasAccessWithLimitedAccess(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'site' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertFalse($home->hasAccess('site'));
		$this->assertFalse($home->hasAccess('pages/test'));
		$this->assertTrue($home->hasAccess('users/test@getkirby.com'));
		$this->assertTrue($home->hasAccess('account'));
	}

	public function testHasValidDomain(): void
	{
		$home = $this->app->panel()->home();
		$uri  = Uri::current();
		$this->assertTrue($home->hasValidDomain($uri));

		$uri = new Uri('/');
		$this->assertTrue($home->hasValidDomain($uri));

		$uri = new Uri('https://getkirby.com');
		$this->assertFalse($home->hasValidDomain($uri));
	}

	public function testRemembered(): void
	{
		$home = $this->app->panel()->home();
		$this->assertNull($home->remembered());
	}

	public function testRememberedFromSession(): void
	{
		$this->app->session()->set('panel.path', 'users');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/users', $home->remembered());
	}

	public function testUrl(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$home = $this->app->panel()->home();
		$this->assertSame('/panel/site', $home->url());
	}

	public static function customHomeProvider(): array
	{
		return [
			// site url: /
			['panel/pages/blog', '/panel/pages/blog'],
			['panel/pages/blog?mean=attack', '/panel/pages/blog'],
			['panel/pages/blog/mean:attack', '/panel/pages/blog'],
			['{{ site.find("blog").panel.url }}', '/panel/pages/blog'],
			['{{ site.url }}', '/'],

			// site url: https://getkirby.com
			['panel/pages/blog', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
			['panel/pages/blog?mean=attack', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
			['panel/pages/blog/mean:attack', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
			['{{ site.find("blog").panel.url }}', 'https://getkirby.com/panel/pages/blog', 'https://getkirby.com'],
			['{{ site.url }}', 'https://getkirby.com', 'https://getkirby.com'],
		];
	}

	#[DataProvider('customHomeProvider')]
	public function testUrlWithCustomHome($url, $expected, $index = '/'): void
	{
		$this->app = $this->app->clone([
			'urls' => [
				'index' => $index
			],
			'site' => [
				'children' => [
					['slug' => 'blog']
				]
			],
			'blueprints' => [
				'users/admin' => [
					'name' => 'admin',
					'home' => $url
				]
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame($expected, $home->url());
	}

	public function testUrlWithInvalidCustomHome(): void
	{
		$this->app = $this->app->clone([
			'urls' => [
				'index' => '/'
			],
			'site' => [
				'children' => [
					['slug' => 'blog']
				]
			],
			'blueprints' => [
				'users/admin' => [
					'name' => 'admin',
					'home' => 'https://getkirby.com'
				]
			],
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('External URLs are not allowed for Panel redirects');

		$this->app->panel()->home()->url();
	}

	public function testUrlWithRememberedPath(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$this->app->session()->set('panel.path', 'users');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/users', $home->url());
	}

	public function testUrlWithInvalidRememberedPath(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');
		$this->app->session()->set('panel.path', 'login');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/site', $home->url());
	}

	public function testUrlWithMissingSiteAccess(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'site' => false,
						]
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/users', $home->url());
	}

	public function testUrlWithAccountAccessOnly(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'site'  => false,
							'users' => false,
							'system' => false
						]
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/account', $home->url());
	}

	public function testUrlWithoutUser(): void
	{
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/login', $home->url());
	}
}
