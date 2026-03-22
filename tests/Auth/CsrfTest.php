<?php

namespace Kirby\Auth;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Csrf::class)]
class CsrfTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Csrf';

	protected Csrf $csrf;

	public function setUp(): void
	{
		parent::setUp();
		$this->csrf = new Csrf($this->app);
	}

	public function testFromSession1(): void
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = [];
		$this->assertFalse($this->csrf->get());
	}

	public function testFromSession2(): void
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => ''];
		$this->assertFalse($this->csrf->get());
	}

	public function testFromSession3(): void
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'session-csrf'];
		$this->assertSame('session-csrf', $this->csrf->get());
	}

	public function testFromSession4(): void
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'invalid-csrf'];
		$this->assertFalse($this->csrf->get());
	}

	public function testGet1(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);

		$this->csrf = new Csrf($this->app);
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = [];
		$this->assertFalse($this->csrf->get());
	}

	public function testGet2(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);

		$this->csrf = new Csrf($this->app);
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'option-csrf'];
		$this->assertSame('option-csrf', $this->csrf->get());
	}

	public function testGet3(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);

		$this->csrf = new Csrf($this->app);
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'session-csrf'];
		$this->assertFalse($this->csrf->get());
	}

	public function testGet4(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf' => 'option-csrf'
			]
		]);

		$this->csrf = new Csrf($this->app);
		$this->app->session()->set('kirby.csrf', 'session-csrf');

		$_GET = ['csrf' => 'invalid-csrf'];
		$this->assertFalse($this->csrf->get());
	}

	public function testCsrfFromSessionPanelDevOption(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel.dev' => true
			]
		]);

		$this->csrf = new Csrf($this->app);

		$this->assertFalse($this->csrf->get());
	}
}
