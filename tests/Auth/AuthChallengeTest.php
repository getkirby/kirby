<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Auth\Exception\RateLimitException;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(Auth::class)]
class AuthChallengeTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.AuthChallenge';

	public function setUp(): void
	{
		parent::setUp();

		$password = User::hashPassword('springfield123');

		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['email'],
					'trials'     => 3
				]
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => $password
				],
				[
					'email' => 'test@exämple.com',
					'id'    => 'idn'
				],
				[
					'email' => 'invalid@example.com',
					'id'    => 'invalid'
				]
			]
		]);

		F::write(static::TMP . '/site/accounts/marge/.htpasswd', $password);

		$this->auth = $this->app->auth();
	}

	public function testCreateChallenge(): void
	{
		$status  = $this->auth->createChallenge('marge@simpsons.com');
		$session = $this->app->session();

		$this->assertSame('pending', $status->state()->value);
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
	}

	public function testCreateChallengeInvalidUser(): void
	{
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
		$this->auth = $this->app->auth();
		$status  = $this->auth->createChallenge('lisa@simpsons.com');
		$session = $this->app->session();

		$this->assertSame('pending', $status->state()->value);
		$this->assertNull($status->challenge(false));
		$this->assertSame('lisa@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.type'));
	}

	public function testCreateChallengeRateLimitingLog(): void
	{
		F::remove(static::TMP . '/site/accounts/.logins');
		$this->app->visitor()->ip('10.3.123.234');

		$this->auth->createChallenge('marge@simpsons.com');

		$log   = $this->auth->limits()->log();
		$ip    = $this->app->visitor()->ip(hash: true);

		$this->assertSame(1, $log['by-ip'][$ip]['trials'] ?? null);
		$this->assertSame(1, $log['by-email']['marge@simpsons.com']['trials'] ?? null);
	}

	public function testCreateChallengeCustomTimeout(): void
	{
		$this->app  = $this->app->clone([
			'options' => [
				'auth' => [
					'challenge'  => [
						'timeout' => 42
					],
					'challenges' => ['email'],
					'trials'     => 3
				]
			]
		]);
		$this->auth = $this->app->auth();

		$session = $this->app->session();
		$time    = time();

		$this->auth->createChallenge('marge@simpsons.com');

		$timeout = $session->get('kirby.challenge.timeout');
		$this->assertGreaterThanOrEqual($time + 42, $timeout);
		$this->assertLessThanOrEqual($time + 43, $timeout);
	}

	public function testLogin2fa(): void
	{
		$status = $this->auth->login2fa('marge@simpsons.com', 'springfield123');
		$this->assertSame('pending', $status->state()->value);
		$this->assertSame('email', $status->challenge(false));
	}

	public function testLogin2faInvalidUser(): void
	{
		$this->app  = $this->app->clone([
			'options' => [
				'auth' => [
					'debug'      => false,
					'challenges' => ['email'],
					'trials'     => 3
				]
			]
		]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->auth->login2fa('lisa@simpsons.com', 'springfield123');
	}

	public function testVerifyChallenge(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => User::hashPassword('123456')]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', time() + 60);

		$this->assertSame(
			$this->app->user('marge@simpsons.com'),
			$this->auth->verifyChallenge('123456')
		);

		$data = $session->data()->get();
		$this->assertSame('marge', $data['kirby.userId'] ?? null);
	}

	public function testVerifyChallengeNoChallenge(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->auth->verifyChallenge('123456');
	}

	public function testVerifyChallengeRateLimited(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => User::hashPassword('123456')]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', time() + 1000);

		F::write(static::TMP . '/site/accounts/.logins', json_encode([
			'by-ip' => [],
			'by-email' => [
				'marge@simpsons.com' => [
					'time'   => time(),
					'trials' => 3
				]
			]
		]));

		$this->expectException(RateLimitException::class);
		$this->auth->verifyChallenge('123456');
	}

	public function testVerifyChallengeTimeLimited(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => User::hashPassword('123456')]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', time() - 10);

		try {
			$this->auth->verifyChallenge('123456');
			$this->fail('The challenge should have timed out');
		} catch (ChallengeTimeoutException) {
			$this->assertNull($session->get('kirby.challenge.email'));
			$this->assertNull($session->get('kirby.challenge.type'));
		}
	}

	public function testVerifyChallengeInvalidCode(): void
	{
		F::remove(static::TMP . '/site/accounts/.logins');
		$this->app->visitor()->ip('10.3.123.234');

		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => User::hashPassword('123456')]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', time() + 1000);

		try {
			$this->auth->verifyChallenge('000000');
			$this->fail('The challenge verification should have failed');
		} catch (PermissionException) {
			$log = $this->auth->limits()->log();
			$ip  = $this->app->visitor()->ip(hash: true);

			$this->assertSame(1, $log['by-ip'][$ip]['trials'] ?? null);
			$this->assertSame(1, $log['by-email']['marge@simpsons.com']['trials'] ?? null);
		}
	}

	public function testVerifyChallengeReturnsNullIfFailDoesNotThrow(): void
	{
		// custom auth that swallows fail() to reach the null return
		$auth = new class($this->app) extends Auth {
			protected function fail(Throwable $exception, Throwable|null $fallback = null): void
			{
				// intentionally ignore
			}
		};

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => User::hashPassword('123456')]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->assertNull($auth->verifyChallenge('654321'));
	}
}
