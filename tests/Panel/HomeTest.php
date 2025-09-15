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

#[CoversClass(Home::class)]
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->assertSame('/panel/site', Home::alternative($user));
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->assertSame('/panel/users', Home::alternative($user));
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->assertSame('/panel/account', Home::alternative($user));
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->assertSame('/', Home::alternative($user));
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('There’s no available Panel page to redirect to');

		Home::alternative($user);
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->assertTrue(Home::hasAccess($user, 'site'));
		$this->assertTrue(Home::hasAccess($user, 'pages/test'));
		$this->assertTrue(Home::hasAccess($user, 'users/test@getkirby.com'));
		$this->assertTrue(Home::hasAccess($user, 'account'));

		// dialogs and dropdowns never get access
		$this->assertFalse(Home::hasAccess($user, 'dialogs/users/create'));
		$this->assertFalse(Home::hasAccess($user, 'dropdowns/users/test@getkirby.com'));

		// invalid routes return false
		$this->assertFalse(Home::hasAccess($user, 'does/not/exist/at/all'));

		// unauthenticated views return true
		$this->assertTrue(Home::hasAccess($user, 'browser'));
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

		$user = $this->app->impersonate('test@getkirby.com');

		$this->assertFalse(Home::hasAccess($user, 'site'));
		$this->assertFalse(Home::hasAccess($user, 'pages/test'));
		$this->assertTrue(Home::hasAccess($user, 'users/test@getkirby.com'));
		$this->assertTrue(Home::hasAccess($user, 'account'));
	}

	public function testHasValidDomain(): void
	{
		$uri = Uri::current();
		$this->assertTrue(Home::hasValidDomain($uri));

		$uri = new Uri('/');
		$this->assertTrue(Home::hasValidDomain($uri));

		$uri = new Uri('https://getkirby.com');
		$this->assertFalse(Home::hasValidDomain($uri));
	}

	public function testIsPanelUrl(): void
	{
		$this->assertTrue(Home::isPanelUrl('/panel'));
		$this->assertTrue(Home::isPanelUrl('/panel/pages/test'));
		$this->assertFalse(Home::isPanelUrl('test'));
	}

	public function testPanelPath(): void
	{
		$this->assertSame('site', Home::panelPath('/panel/site'));
		$this->assertSame('pages/test', Home::panelPath('/panel/pages/test'));
		$this->assertSame('', Home::panelPath('/test/page'));
	}

	public function testRemembered(): void
	{
		$this->assertNull(Home::remembered());
	}

	public function testRememberedFromSession(): void
	{
		$this->app->session()->set('panel.path', 'users');
		$this->assertSame('/panel/users', Home::remembered());
	}

	public function testUrl(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'test@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->assertSame('/panel/site', Home::url());
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

		$this->assertSame($expected, Home::url());
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

		Home::url();
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

		$this->assertSame('/panel/users', Home::url());
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

		$this->assertSame('/panel/site', Home::url());
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
		$this->assertSame('/panel/users', Home::url());
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

		$this->assertSame('/panel/account', Home::url());
	}

	public function testUrlWithoutUser(): void
	{
		$this->assertSame('/panel/login', Home::url());
	}
}
