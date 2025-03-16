<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\LanguagePermissions
 */
class LanguagePermissionsTest extends TestCase
{
	public static function actionProvider(): array
	{
		return [
			['create'],
			['delete'],
			['update'],
		];
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::can
	 * @dataProvider actionProvider
	 */
	public function testWithAdmin($action)
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$kirby->impersonate('kirby');

		$language = new Language(['code' => 'en']);
		$perms    = $language->permissions();

		$this->assertTrue($perms->can($action));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::can
	 * @dataProvider actionProvider
	 */
	public function testWithNobody($action)
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			]
		]);

		$language = new Language(['code' => 'en']);
		$perms    = $language->permissions();

		$this->assertFalse($perms->can($action));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::can
	 * @dataProvider actionProvider
	 */
	public function testWithNoAdmin($action)
	{
		$app = new App([
			'languages' => [
				[
					'code' => 'en'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin'],
				[
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'create' => false,
							'delete' => false,
							'update' => false
						],
					]
				]
			],
			'user'  => 'editor@getkirby.com',
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
		]);

		$language = $app->language('en');
		$perms    = $language->permissions();

		$this->assertSame('editor', $app->role()->name());
		$this->assertFalse($perms->can($action));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::canFromCache
	 */
	public function testCanFromCache()
	{
		$app = new App([
			'languages' => [
				[
					'code' => 'en'
				]
			],
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'access' => false,
							'list'   => false
						],
					]
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				['id' => 'bastian', 'role' => 'editor'],
			]
		]);

		$app->impersonate('bastian');

		$language = $app->language('en');

		$this->assertFalse(LanguagePermissions::canFromCache($language, 'access'));
		$this->assertFalse(LanguagePermissions::canFromCache($language, 'access'));
		$this->assertFalse(LanguagePermissions::canFromCache($language, 'list'));
		$this->assertFalse(LanguagePermissions::canFromCache($language, 'list'));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::canFromCache
	 */
	public function testCanFromCacheDynamic()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Cannot use permission cache for dynamically-determined permission');

		$app = new App([
			'languages' => [
				[
					'code' => 'en'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		$language = $app->language('en');

		LanguagePermissions::canFromCache($language, 'delete');
	}

	/**
	 * @covers ::canDelete
	 */
	public function testCanDeleteWhenNotDeletable()
	{
		$app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'roles' => [
				['name' => 'admin']
			]
		]);

		$app->impersonate('kirby');

		$language = $app->language('en');
		$perms    = $language->permissions();

		$this->assertFalse($perms->can('delete'));
	}
}
