<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui';

	protected function setUp(): void
	{
		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			],
			'user' => 'test@getkirby.com'
		]);
	}

	protected function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		App::destroy();

		Blueprint::$loaded = [];

		$this->tearDownTmp();

		// clear fake json requests
		$_GET = [];
	}
}
