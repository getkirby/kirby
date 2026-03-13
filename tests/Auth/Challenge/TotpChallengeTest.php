<?php

namespace Kirby\Auth\Challenge;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Auth\TestCase;
use Kirby\Cms\User;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Challenge::class)]
#[CoversClass(TotpChallenge::class)]
class TotpChallengeTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.TotpChallenge';

	protected User $user;
	protected string $secret;

	public function setUp(): void
	{
		parent::setUp();

		$this->secret = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'marge@simpsons.com',
					'id'    => 'marge',
				],
			]
		]);

		F::write(
			static::TMP . '/site/accounts/marge/.htpasswd',
			"\n" . json_encode(['totp' => $this->secret])
		);

		$this->user = $this->app->user('marge');
	}

	public function testCreate(): void
	{
		$challenge = new TotpChallenge($this->user, 'login', 600);
		$this->assertNull($challenge->create());
	}

	public function testIsAvailable(): void
	{
		$this->assertTrue(TotpChallenge::isAvailable($this->user, 'login'));

		F::write(static::TMP . '/site/accounts/marge/.htpasswd', '');
		$this->assertFalse(TotpChallenge::isAvailable($this->user, 'login'));
	}

	public function testMode(): void
	{
		$challenge = new TotpChallenge($this->user, '2fa', 300);
		$this->assertSame('2fa', $challenge->mode());
	}

	public function testSettings(): void
	{
		$settings = TotpChallenge::settings($this->user);
		$this->assertCount(1, $settings);
	}

	public function testTimeout(): void
	{
		$challenge = new TotpChallenge($this->user, '2fa', 300);
		$this->assertSame(300, $challenge->timeout());
	}

	public function testType(): void
	{
		$challenge = new TotpChallenge($this->user, '2fa', 300);
		$this->assertSame('totp', $challenge->type());
	}

	public function testUser(): void
	{
		$challenge = new TotpChallenge($this->user, '2fa', 300);
		$this->assertSame($this->user, $challenge->user());
	}

	public function testVerify(): void
	{
		$challenge = new TotpChallenge($this->user, 'login', 600);
		$totp      = new Totp($this->secret);
		$code      = $totp->generate();

		$this->assertTrue($challenge->verify($code, new Pending()));
		$this->assertFalse($challenge->verify('000000', new Pending()));
	}
}
