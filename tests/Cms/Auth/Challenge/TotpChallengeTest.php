<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Cms\TestCase;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Totp;

/**
 * @coversDefaultClass \Kirby\Cms\Auth\TotpChallenge
 */
class TotpChallengeTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Auth.TotpChallenge';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email' => 'homer@simpsons.com',
					'name'  => 'Homer'
				]
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::isAvailable
	 */
	public function testIsAvailable()
	{
		$user = $this->app->user('homer@simpsons.com');
		$this->assertFalse(TotpChallenge::isAvailable($user, 'login'));
		$this->app->impersonate('kirby', fn () => $user->changeTotp('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
		$this->assertTrue(TotpChallenge::isAvailable($user, 'login'));
	}

	/**
	 * @covers ::create
	 */
	public function testCreate()
	{
		$user = $this->app->user('homer@simpsons.com');
		$this->assertNull(TotpChallenge::create($user, []));
	}

	/**
	 * @covers ::verify
	 */
	public function testVerify()
	{
		$user = $this->app->user('homer@simpsons.com');
		$totp = new Totp();
		$this->app->impersonate('kirby', fn () => $user->changeTotp($totp->secret()));

		$this->assertTrue(TotpChallenge::verify($user, $totp->generate()));
		$this->assertFalse(TotpChallenge::verify($user, 'a23456'));
	}
}
