<?php

namespace Kirby\Cms\Api;

use Kirby\Cms\App;
use Kirby\Cms\TestCase as TestCase;
use Kirby\Filesystem\Dir;

class ApiCollectionTestCase extends TestCase
{
	protected $api;
	protected $app;

	public function setUp(): void
	{
		if ($this->hasTmp() === true) {
			Dir::remove(static::TMP);
		}

		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null',
			],
		]);

		$this->api = $this->app->api();
	}

	public function tearDown(): void
	{
		App::destroy();

		if ($this->hasTmp() === true) {
			Dir::remove(static::TMP);
		}
	}
}
