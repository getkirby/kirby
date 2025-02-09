<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;

#[CoversClass(PagePermissions::class)]
#[CoversClass(ModelPermissions::class)]
class PagePermissionsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['id' => 'bastian', 'role' => 'admin']
			]
		]);
	}

	public function tearDown(): void
	{
		$prop = new ReflectionProperty(PagePermissions::class, 'cache');
		$prop->setValue(null, []);
	}

	public static function actionProvider(): array
	{
		return [
			['access'],
			['changeSlug'],
			['changeStatus'],
			// ['changeTemplate'], Tested separately because of the needed blueprints
			['changeTitle'],
			['create'],
			['delete'],
			['duplicate'],
			['list'],
			['move'],
			['preview'],
			['sort'],
			['update'],
		];
	}

	#[DataProvider('actionProvider')]
	public function testWithAdmin(string $action)
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertTrue($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithAdminButDisabledOption(string $action)
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => false
				]
			]
		]);

		$this->assertFalse($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithEditorAndPositiveWildcard(string $action)
	{
		$app = $this->app->clone([
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => [
						'*' => true
					]
				]
			]
		]);

		$this->assertTrue($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithEditorAndPositivePermission(string $action)
	{
		$app = $this->app->clone([
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => [
						'*' => false,
						'editor' => true
					]
				]
			]
		]);

		$this->assertTrue($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithEditorAndNegativeWildcard(string $action)
	{
		$app = $this->app->clone([
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => [
						'*' => false
					]
				]
			]
		]);

		$this->assertFalse($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithEditorAndNegativePermission(string $action)
	{
		$app = $this->app->clone([
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => [
						'*' => true,
						'editor' => false
					]
				]
			]
		]);

		$this->assertFalse($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithAdminAndNegativeOptionForOtherRole(string $action)
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => [
						'visitor' => false
					]
				]
			]
		]);

		$this->assertTrue($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithAdminAndNegativeOptionForOtherRoleAndNegativeFallback(string $action)
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
			'blueprint' => [
				'name' => 'test',
				'options' => [
					$action => [
						'*'       => false,
						'visitor' => false
					]
				]
			]
		]);

		$this->assertFalse($page->permissions()->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNobody(string $action)
	{
		$page  = new Page(['slug' => 'test']);
		$perms = $page->permissions();

		$this->assertFalse($perms->can($action));
	}

	public function testCanFromCache()
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug'      => 'test',
			'num'       => 1,
			'template'  => 'some-template',
			'blueprint' => [
				'name' => 'some-template',
				'options' => [
					'access' => false,
					'list'   => false
				]
			]
		]);

		$this->assertFalse(PagePermissions::canFromCache($page, 'access'));
		$this->assertFalse(PagePermissions::canFromCache($page, 'access'));
		$this->assertFalse(PagePermissions::canFromCache($page, 'list'));
		$this->assertFalse(PagePermissions::canFromCache($page, 'list'));
	}

	public function testCanFromCacheDynamic()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot use permission cache for dynamically-determined permission');

		$page = new Page([
			'slug'     => 'test',
			'num'      => 1,
			'template' => 'some-template',
		]);

		PagePermissions::canFromCache($page, 'changeTemplate');
	}

	public function testCannotChangeTemplate()
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFalse($page->permissions()->can('changeTemplate'));
	}

	public function testCanChangeTemplate()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A'
				],
				'pages/b' => [
					'title' => 'B'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'b'
					]
				]
			]
		]);

		$this->assertTrue($page->permissions()->can('changeTemplate'));
	}

	public function testCanChangeTemplateHomeError()
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'home',
						'blueprint' => [
							'options' => [
								'template' => [
									'a',
									'b'
								]
							]
						]
					],
					[
						'slug' => 'error',
						'blueprint' => [
							'options' => [
								'template' => [
									'a',
									'b'
								]
							]
						]
					]
				]
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A'
				],
				'pages/b' => [
					'title' => 'B'
				]
			]
		]);

		$this->app->impersonate('kirby');
		$home  = $this->app->site()->find('home');
		$error = $this->app->site()->find('error');

		$this->assertTrue($home->permissions()->can('changeTemplate'));
		$this->assertFalse($error->permissions()->can('changeTemplate'));
	}

	public function testCanSortListedPages()
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertTrue($page->permissions()->can('sort'));
	}

	public function testCanNotFoundDefault()
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertFalse($page->permissions()->can('foo'));
		$this->assertTrue($page->permissions()->can('foo', true));
	}

	public function testCannotSortUnlistedPages()
	{
		$this->app->impersonate('kirby');

		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertFalse($page->permissions()->can('sort'));
	}

	public function testCannotSortErrorPage()
	{
		$this->app->impersonate('kirby');

		$site = new Site([
			'children' => [
				[
					'slug' => 'error',
					'num'  => 1
				]
			]
		]);

		$page = $site->find('error');

		$this->assertFalse($page->permissions()->can('sort'));
	}

	public function testCannotSortPagesWithSortMode()
	{
		$this->app->impersonate('kirby');

		// sort mode: zero
		$page = new Page([
			'slug' => 'test',
			'num'  => 0,
			'blueprint' => [
				'num' => 'zero'
			]
		]);

		$this->assertFalse($page->permissions()->can('sort'));

		// sort mode: date
		$page = new Page([
			'slug' => 'test',
			'num'  => 20161121,
			'blueprint' => [
				'num' => 'date'
			]
		]);

		$this->assertFalse($page->permissions()->can('sort'));

		// sort mode: custom
		$page = new Page([
			'slug' => 'test',
			'num'  => 2012,
			'blueprint' => [
				'num' => '{{ page.year }}'
			]
		]);

		$this->assertFalse($page->permissions()->can('sort'));
	}

	public function testCannotNotFoundDefault()
	{
		$this->app->impersonate('bastian');

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertTrue($page->permissions()->cannot('foo'));
		$this->assertFalse($page->permissions()->cannot('foo', false));
	}
}
