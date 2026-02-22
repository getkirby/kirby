<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Email\Email;
use Kirby\Filesystem\Dir;
use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP = KIRBY_TMP_DIR . '/Auth';

	protected Auth $auth;

	public function setUp(): void
	{
		Email::$debug = true;
		Email::$emails = [];

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'server' => [
				'SERVER_NAME' => 'getkirby.com',
			],
			'options' => [
				'auth' => [
					'debug' => true,
				]
			]
		]);

		$this->auth = $this->app->auth();

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Email::$debug = false;
		Email::$emails = [];
		$_GET = [];
		Dir::remove(static::TMP);
		App::destroy();
	}
}
