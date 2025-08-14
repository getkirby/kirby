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
}
