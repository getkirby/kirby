<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

class STest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.STest';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testInstance(): void
	{
		$this->assertSame($this->app->session(), S::instance());
	}
}
