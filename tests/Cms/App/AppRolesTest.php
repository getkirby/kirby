<?php

namespace Kirby\Cms;

class AppRolesTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testSet()
	{
		$app = new App([
			'roles' => [
				[
					'name'  => 'editor',
					'title' => 'Editor'
				]
			]
		]);

		$this->assertCount(2, $app->roles());
		$this->assertSame('editor', $app->roles()->last()->name());
	}

	public function testLoad()
	{
		$app = new App([
			'roots' => [
				'site' => static::FIXTURES
			]
		]);

		$this->assertCount(2, $app->roles());
		$this->assertSame('editor', $app->roles()->last()->name());
	}
}
