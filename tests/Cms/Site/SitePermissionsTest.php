<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Site::class)]
class SitePermissionsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.SitePermissions';

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

	#[DataProvider('actionProvider')]
	public function testWithEditorAndDisabledPermission(string $action): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name' => 'editor',
					'permissions' => [
						'site' => [
							$action => false
						]
					]
				]
			],
			'users' => [
				['id' => 'bastian', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('bastian');

		$permissions = $this->app->site()->permissions();

		$this->assertFalse($permissions->can($action));
	}

	public function testCanWithDefault(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['id' => 'bastian', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('bastian');

		$permissions = $this->app->site()->permissions();

		// an action that is not defined in the permissions map
		// falls back to the passed default…
		$this->assertTrue($permissions->can('undefined', true));

		// …as well as a different default for the same action
		$this->assertFalse($permissions->can('undefined', false));
	}
}
