<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class SitePermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SitePermissions';

	public static function actionProvider(): array
	{
		return [
			['access'],
			['changeTitle'],
			['update'],
		];
	}

	#[DataProvider('actionProvider')]
	public function testWithAdmin(string $action): void
	{
		$this->app->impersonate('kirby');

		$site        = new Site();
		$permissions = $site->permissions();

		$this->assertTrue($permissions->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNobody(string $action): void
	{
		$this->app->impersonate('nobody');

		$site        = new Site();
		$permissions = $site->permissions();

		$this->assertFalse($permissions->can($action));
	}

	/**
	 * @covers \Kirby\Cms\ModelPermissions::canFromCache
	 */
	public function testCanFromCache()
	{
		$app = new App([
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'site' => [
							'access' => false
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

		$site = $app->site();

		$this->assertFalse(SitePermissions::canFromCache($site, 'access'));
		$this->assertFalse(SitePermissions::canFromCache($site, 'access'));
	}
}
