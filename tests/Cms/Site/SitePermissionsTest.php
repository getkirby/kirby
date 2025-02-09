<?php

namespace Kirby\Cms;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SitePermissionsTest extends TestCase
{
	public static function actionProvider(): array
	{
		return [
			['changeTitle'],
			['update'],
		];
	}

	#[DataProvider('actionProvider')]
	public function testWithAdmin(string $action)
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$kirby->impersonate('kirby');

		$site  = new Site();
		$perms = $site->permissions();

		$this->assertTrue($perms->can($action));
	}

	#[DataProvider('actionProvider')]
	public function testWithNobody(string $action)
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$site  = new Site();
		$perms = $site->permissions();

		$this->assertFalse($perms->can($action));
	}
}
