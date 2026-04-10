<?php

namespace Kirby\Cms;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SitePermissionsTest extends TestCase
{
	public static function actionProvider(): array
	{
		return [
			['access'],
			['changeTitle'],
			['update'],
		];
	}

	/**
	 * @dataProvider actionProvider
	 */
	#[DataProvider('actionProvider')]
	public function testWithAdmin($action)
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

	/**
	 * @dataProvider actionProvider
	 */
	#[DataProvider('actionProvider')]
	public function testWithNobody($action)
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
