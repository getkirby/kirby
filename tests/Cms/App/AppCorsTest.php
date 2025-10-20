<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(App::class)]
class AppCorsTest extends TestCase
{
	public function testCorsDefault(): void
	{
		$this->assertFalse($this->app->cors());
	}

	public function testCorsWhenEnabled(): void
	{
		$app = $this->app->clone([
			'options' => [
				'cors' => [
					'enabled' => true
				]
			]
		]);

		$this->assertTrue($app->cors());
	}
}
