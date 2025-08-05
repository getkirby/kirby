<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Filesystem\Dir;
use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel';

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Blueprint::$loaded = [];
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);

		App::destroy();
	}

	public function setRequest(array $data = []): App
	{
		return $this->app = $this->app->clone([
			'request' => [
				'query' => $data
			]
		]);
	}
}
