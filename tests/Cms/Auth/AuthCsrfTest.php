<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthCsrfTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.AuthCsrf';

	protected Auth $auth;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		$this->auth = new Auth($this->app);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
		$_GET = [];
	}

	public function testCsrfFromSession1()
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = [];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromSession2()
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => ''];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromSession3()
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'session-csrf'];
		$this->assertSame('session-csrf', $this->auth->csrf());
	}

	public function testCsrfFromSession4()
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'invalid-csrf'];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromOption1()
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = [];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromOption2()
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'option-csrf'];
		$this->assertSame('option-csrf', $this->auth->csrf());
	}

	public function testCsrfFromOption3()
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'session-csrf'];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromOption4()
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);
		$this->auth = new Auth($this->app);

		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'invalid-csrf'];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromSessionPanelDevOption()
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel.dev' => true
			]
		]);
		$this->auth = new Auth($this->app);
		$this->assertSame('dev', $this->auth->csrfFromSession());
	}
}
