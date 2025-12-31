<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Auth\Challenges;
use Kirby\Cms\Auth\ErrorneousChallenge;
use Kirby\Email\Email;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(Auth::class)]
class AuthChallengeTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.AuthChallenge';

	protected Auth $auth;
	public string|null $failedEmail = null;

	public function setUp(): void
	{
		Challenges::$challenges['errorneous'] = ErrorneousChallenge::class;
		Email::$debug = true;
		Email::$emails = [];
		$_SERVER['SERVER_NAME'] = 'kirby.test';

		$self = $this;

		$this->app = new App([
			'hooks' => [
				'user.login:failed' => function ($email) use ($self) {
					$self->failedEmail = $email;
				}
			],
			'options' => [
				'auth' => [
					'challenges' => ['errorneous', 'email'],
					'debug'      => true,
					'trials'     => 3
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => password_hash('springfield123', PASSWORD_DEFAULT)
				],
				[
					'email' => 'test@exämple.com',
					'id'    => 'idn'
				],
				[
					'email' => 'error@getkirby.com',
					'id'    => 'error'
				]
			]
		]);
		Dir::make(static::TMP . '/site/accounts');

		$this->auth = $this->app->auth();
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);

		unset(Challenges::$challenges['errorneous']);
		Email::$debug = false;
		Email::$emails = [];
		unset($_SERVER['SERVER_NAME']);
		$this->failedEmail = null;
	}

	public function testCreateChallenge(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => false
				]
			]
		]);
		$auth    = $this->app->auth();
		$session = $this->app->session();

		$this->app->visitor()->ip('10.1.123.234');

		// existing user
		$status = $auth->createChallenge('marge@simpsons.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame(1800, $session->timeout());
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
		preg_match('/^[0-9]{3} [0-9]{3}$/m', Email::$emails[0]->body()->text(), $codeMatches);
		$this->assertTrue(password_verify(
			str_replace(' ', '', $codeMatches[0]),
			$session->get('kirby.challenge.data')['secret']
		));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertNull($this->failedEmail);
		$session->clear();

		// non-existing user
		$status = $auth->createChallenge('invalid@example.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'invalid@example.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertNull($status->challenge(false));
		$this->assertSame('invalid@example.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertSame('invalid@example.com', $this->failedEmail);

		// error in the challenge
		$status = $auth->createChallenge('error@getkirby.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'error@getkirby.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertNull($status->challenge(false));
		$this->assertSame('error@getkirby.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertSame('invalid@example.com', $this->failedEmail); // a challenge error is not considered a failed login

		// verify rate-limiting log
		$data = [
			'by-ip' => [
				'87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
					'trials' => 3,
					'time'   => MockTime::$time,
				]
			],
			'by-email' => [
				'marge@simpsons.com' => [
					'trials' => 1,
					'time'   => MockTime::$time,
				],
				'error@getkirby.com' => [
					'trials' => 1,
					'time'   => MockTime::$time,
				]
			]
		];
		$this->assertSame($data, $auth->log());

		// fake challenge when rate-limited
		$status = $auth->createChallenge('marge@simpsons.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertNull($status->challenge(false));
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testCreateChallengeDebugError(): void
	{
		$auth = $this->app->auth();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('An error occurred in the challenge');
		$auth->createChallenge('error@getkirby.com');
	}

	public function testCreateChallengeDebugNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "invalid@example.com" cannot be found');

		$this->auth->createChallenge('invalid@example.com');
	}

	public function testCreateChallengeDebugRateLimit(): void
	{
		$auth = $this->app->auth();

		$auth->createChallenge('marge@simpsons.com');
		$auth->createChallenge('marge@simpsons.com');
		$auth->createChallenge('marge@simpsons.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Rate limit exceeded');
		$auth->createChallenge('marge@simpsons.com');
	}

	public function testCreateChallengeCustomTimeout(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenge.timeout' => 10,
					'debug' => false
				]
			]
		]);
		$auth    = $this->app->auth();
		$session = $this->app->session();

		// existing user
		$status = $auth->createChallenge('marge@simpsons.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame(MockTime::$time + 10, $session->get('kirby.challenge.timeout'));
		$session->clear();

		// non-existing user
		$status = $auth->createChallenge('invalid@example.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'invalid@example.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertNull($status->challenge(false));
		$this->assertSame(MockTime::$time + 10, $session->get('kirby.challenge.timeout'));
	}

	public function testCreateChallengeLong(): void
	{
		$session = $this->app->session();

		$status = $this->auth->createChallenge('marge@simpsons.com', true);
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));

		$this->assertFalse($session->timeout());
	}

	public function testCreateChallengeWithPunycodeEmail(): void
	{
		$session = $this->app->session();

		$status = $this->auth->createChallenge('test@xn--exmple-cua.com');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'test@exämple.com',
			'mode'      => 'login',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame('test@exämple.com', $session->get('kirby.challenge.email'));
	}

	public function testEnabledChallenges(): void
	{
		// default
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => null
				]
			]
		]);
		$this->assertSame(['totp', 'email'], $app->auth()->enabledChallenges());

		// a single challenge
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => 'totp'
				]
			]
		]);
		$this->assertSame(['totp'], $app->auth()->enabledChallenges());

		// multiple challenges
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['totp', 'email']
				]
			]
		]);
		$this->assertSame(['totp', 'email'], $app->auth()->enabledChallenges());
	}

	public function testLogin2fa(): void
	{
		$session = $this->app->session();

		$status = $this->auth->login2fa('marge@simpsons.com', 'springfield123');
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => '2fa',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertSame(1800, $session->timeout());
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('2fa', $session->get('kirby.challenge.mode'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
		preg_match('/^[0-9]{3} [0-9]{3}$/m', Email::$emails[0]->body()->text(), $codeMatches);
		$this->assertTrue(password_verify(
			str_replace(' ', '', $codeMatches[0]),
			$session->get('kirby.challenge.data')['secret']
		));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertNull($this->failedEmail);
	}

	public function testLogin2faLong(): void
	{
		$session = $this->app->session();

		$status = $this->auth->login2fa('marge@simpsons.com', 'springfield123', true);
		$this->assertSame([
			'challenge' => 'email',
			'data'      => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => '2fa',
			'status'    => 'pending'
		], $status->toArray());
		$this->assertSame('email', $status->challenge(false));
		$this->assertFalse($session->timeout());
		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('2fa', $session->get('kirby.challenge.mode'));
		$this->assertSame('email', $session->get('kirby.challenge.type'));
		preg_match('/^[0-9]{3} [0-9]{3}$/m', Email::$emails[0]->body()->text(), $codeMatches);
		$this->assertTrue(password_verify(
			str_replace(' ', '', $codeMatches[0]),
			$session->get('kirby.challenge.data')['secret']
		));
		$this->assertSame(MockTime::$time + 600, $session->get('kirby.challenge.timeout'));
		$this->assertNull($this->failedEmail);
	}

	public function testLogin2faInvalidUser(): void
	{
		$session = $this->app->session();

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "invalid@example.com" cannot be found');
		$this->auth->login2fa('invalid@example.com', 'springfield123');
	}

	public function testLogin2faInvalidPassword(): void
	{
		$session = $this->app->session();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');
		$this->auth->login2fa('marge@simpsons.com', 'springfield456');
	}

	public function testVerifyChallenge(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->assertSame(
			$this->app->user('marge@simpsons.com'),
			$this->auth->verifyChallenge('123456')
		);
		$this->assertSame(['kirby.userId' => 'marge'], $session->data()->get());
	}

	public function testVerifyChallengePasswordReset(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'password-reset');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->assertSame(
			$this->app->user('marge@simpsons.com'),
			$this->auth->verifyChallenge('123456')
		);
		$this->assertSame([
			'kirby.userId'        => 'marge',
			'kirby.resetPassword' => true,
		], $session->data()->get());
	}

	public function testVerifyChallengeNoChallenge1(): void
	{
		try {
			$this->auth->verifyChallenge('123456');

			$this->fail('No InvalidArgumentException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
			$this->assertSame('No authentication challenge is active', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
			$this->assertSame([
				'challenge' => null,
				'data'      => null,
				'email'     => null,
				'mode'      => null,
				'status'    => 'inactive'
			], $this->auth->status()->toArray());
		}
	}

	public function testVerifyChallengeNoChallenge2(): void
	{
		try {
			$this->app->session()->set('kirby.challenge.email', 'marge@simpsons.com');
			$this->auth->verifyChallenge('123456');

			$this->fail('No InvalidArgumentException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf(InvalidArgumentException::class, $e);
			$this->assertSame('No authentication challenge is active', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => false], $e->getDetails());
			$this->assertSame([
				'challenge' => 'email',
				'data'      => null,
				'email'     => 'marge@simpsons.com',
				'mode'      => null,
				'status'    => 'pending'
			], $this->auth->status()->toArray());
		}
	}

	public function testVerifyChallengeNoChallengeNoDebug1(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => false
				]
			]
		]);
		$auth = $this->app->auth();

		try {
			$auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf(PermissionException::class, $e);
			$this->assertSame('Invalid code', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
			$this->assertSame([
				'challenge' => null,
				'data'      => null,
				'email'     => null,
				'mode'      => null,
				'status'    => 'inactive'
			], $this->auth->status()->toArray());
		}
	}

	public function testVerifyChallengeNoChallengeNoDebug2(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => false
				]
			]
		]);
		$auth = $this->app->auth();

		try {
			$this->app->session()->set('kirby.challenge.email', 'marge@simpsons.com');
			$auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf(PermissionException::class, $e);
			$this->assertSame('Invalid code', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => false], $e->getDetails());
		}
	}

	public function testVerifyChallengeInvalidEmail(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "invalid@example.com" cannot be found');

		$this->app->session()->set('kirby.challenge.email', 'invalid@example.com');
		$this->app->session()->set('kirby.challenge.mode', 'login');
		$this->app->session()->set('kirby.challenge.type', 'email');
		$this->auth->verifyChallenge('123456');
	}

	public function testVerifyChallengeRateLimited(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Rate limit exceeded');

		$session = $this->app->session();

		$this->auth->track('marge@simpsons.com');
		$this->auth->track('homer@simpsons.com');
		$this->auth->track('homer@simpsons.com');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');

		$this->auth->verifyChallenge('123456');
	}

	public function testVerifyChallengeTimeLimited(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time - 1);

		try {
			$this->auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf(PermissionException::class, $e);
			$this->assertSame('Authentication challenge timeout', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
		}

		$this->assertSame([
			'challenge' => null,
			'data'      => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());

		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.data'));
		$this->assertNull($session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.timeout'));
	}

	public function testVerifyChallengeTimeLimitedNoDebug(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'debug' => false
				]
			]
		]);
		$auth = $this->app->auth();
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time - 1);

		try {
			$auth->verifyChallenge('123456');

			$this->fail('No PermissionException was thrown');
		} catch (Throwable $e) {
			$this->assertInstanceOf(PermissionException::class, $e);
			$this->assertSame('Invalid code', $e->getMessage());
			$this->assertSame(['challengeDestroyed' => true], $e->getDetails());
		}

		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.data'));
		$this->assertNull($session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertNull($session->get('kirby.challenge.timeout'));
	}

	public function testVerifyChallengeInvalidCode(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid code');

		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->auth->verifyChallenge('654321');
	}

	public function testVerifyChallengeInvalidChallenge(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No auth challenge class for: test');

		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'test');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->auth->verifyChallenge('123456');
	}

	public function testVerifyChallengeReturnsNullIfFailDoesNotThrow(): void
	{
		// custom auth that swallows fail() to reach the null return
		$auth = new class ($this->app) extends Auth {
			protected function fail(Throwable $exception, Throwable|null $fallback = null): void
			{
				// intentionally ignore
			}
		};

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->assertNull($auth->verifyChallenge('654321'));
	}
}
