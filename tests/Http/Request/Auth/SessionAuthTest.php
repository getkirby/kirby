<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Kirby\Http\Request\Auth
 * @covers \Kirby\Http\Request\Auth\SessionAuth
 */
class SessionAuthTest extends TestCase
{
	protected $kirby;
	protected $tmp;

	public function setUp(): void
	{
		$this->tmp = dirname(__DIR__, 2) . '/tmp';
		Dir::make($this->tmp . '/site/sessions');

		$this->kirby = new App([
			'roots' => [
				'index' => $this->tmp
			]
		]);
	}

	public function tearDown(): void
	{
		$this->kirby->session()->destroy();
		Dir::remove($this->tmp);
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
