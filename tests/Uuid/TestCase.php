<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected $fixtures;
	protected $tmp;

	protected function setUp(): void
	{
		$this->fixtures =  __DIR__ . '/fixtures';
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp = __DIR__ . '/tmp',
			]
		]);

		Dir::make($this->tmp);
	}

	protected function tearDown(): void
	{
		Dir::remove($this->tmp);
		Cache::store()->flush();
	}
}
