<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @covers \Kirby\Http\Request\Auth
 * @covers \Kirby\Http\Request\Auth\SessionAuth
 */
class SessionAuthTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Http.Request.Auth.SessionAuth';

	protected $kirby;

	public function setUp(): void
	{
		Dir::make(static::TMP . '/site/sessions');

		$this->kirby = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function tearDown(): void
	{
		$this->kirby->session()->destroy();
		Dir::remove(static::TMP);
	}

	public function testInstance()
	{
		$session = $this->kirby->session();
		$session->ensureToken();

		$auth = new SessionAuth($session->token());

		$this->assertSame('session', $auth->type());
		$this->assertSame($session->token(), $auth->data());
		$this->assertSame($session->token(), $auth->token());
		$this->assertSame($session, $auth->session());
		$this->assertSame('Session ' . $session->token(), (string)$auth);
	}
}
