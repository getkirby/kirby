<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Http\Uri;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Home
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

	/**
	 * @covers ::alternative
	 */
	public function testAlternative()
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

	/**
	 * @covers ::alternative
	 */
	public function testAlternativeWithLimitedAccess()
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

	/**
	 * @covers ::alternative
	 */
	public function testAlternativeWithOnlyAccessToAccounts()
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

	/**
	 * @covers ::alternative
	 */
	public function testAlternativeWithoutPanelAccess()
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

	/**
	 * @covers ::alternative
	 */
	public function testAlternativeWithoutViewAccess()
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

	/**
	 * @covers ::hasAccess
	 */
	public function testHasAccess()
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

	/**
	 * @covers ::hasAccess
	 */
	public function testHasAccessWithLimitedAccess()
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

	/**
	 * @covers ::hasValidDomain
	 */
	public function testHasValidDomain()
	{
		$home = $this->app->panel()->home();
		$uri  = Uri::current();
		$this->assertTrue($home->hasValidDomain($uri));

		$uri = new Uri('/');
		$this->assertTrue($home->hasValidDomain($uri));

		$uri = new Uri('https://getkirby.com');
		$this->assertFalse($home->hasValidDomain($uri));
	}

	/**
	 * @covers ::remembered
	 */
	public function testRemembered()
	{
		$home = $this->app->panel()->home();
		$this->assertNull($home->remembered());
	}

	/**
	 * @covers ::remembered
	 */
	public function testRememberedFromSession()
	{
		$this->app->session()->set('panel.path', 'users');
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/users', $home->remembered());
	}

	/**
	 * @covers ::url
	 */
	public function testUrl()
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

	/**
	 * @dataProvider customHomeProvider
	 */
	public function testUrlWithCustomHome($url, $expected, $index = '/')
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

	/**
	 * @covers ::url
	 */
	public function testUrlWithInvalidCustomHome()
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

	/**
	 * @covers ::url
	 */
	public function testUrlWithRememberedPath()
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

	/**
	 * @covers ::url
	 */
	public function testUrlWithInvalidRememberedPath()
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

	/**
	 * @covers ::url
	 */
	public function testUrlWithMissingSiteAccess()
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

	/**
	 * @covers ::url
	 */
	public function testUrlWithAccountAccessOnly()
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

	/**
	 * @covers ::url
	 */
	public function testUrlWithoutUser()
	{
		$home = $this->app->panel()->home();
		$this->assertSame('/panel/login', $home->url());
	}
}
