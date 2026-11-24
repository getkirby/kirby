<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

class STest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.STest';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testInstance(): void
	{
		$this->assertSame($this->app->session(), S::instance());
	}
}
