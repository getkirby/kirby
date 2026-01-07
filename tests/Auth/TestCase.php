<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Email\Email;
use Kirby\Filesystem\Dir;
use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	public function setUp(): void
	{
		Email::$debug = true;
		Email::$emails = [];
		$_SERVER['SERVER_NAME'] = 'kirby.test';

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'auth' => [
					'debug' => true,
				]
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Email::$debug = false;
		Email::$emails = [];
		unset($_SERVER['SERVER_NAME']);
		$_GET = [];
		Dir::remove(static::TMP);
		App::destroy();
	}
}
