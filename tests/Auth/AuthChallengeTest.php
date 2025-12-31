<?php

namespace Kirby\Auth;

use Kirby\Cms\User;
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

		$password = password_hash('springfield123', PASSWORD_DEFAULT);

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

	public function testValidatePassword(): void
	{
		$user = $this->auth->validatePassword('marge@simpsons.com', 'springfield123');
		$this->assertInstanceOf(User::class, $user);
		$this->assertSame('marge@simpsons.com', $user->email());
	}

	public function testValidatePasswordInvalidUser(): void
	{
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->auth->validatePassword('doesnot@exist.test', 'springfield123');
	}

	public function testValidatePasswordInvalidPassword(): void
	{
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->auth->validatePassword('marge@simpsons.com', 'wrong-password');
	}

	public function testLogin2fa(): void
	{
		$status = $this->auth->login2fa('marge@simpsons.com', 'springfield123');
		$this->assertSame('pending', $status->state()->value);
		$this->assertSame('email', $status->challenge(false));
	}

	public function testVerifyChallenge(): void
	{
		$session = $this->app->session();

		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
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
		$session->set('kirby.challenge.data', ['secret' => password_hash('123456', PASSWORD_DEFAULT)]);
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.timeout', MockTime::$time + 1);

		$this->assertNull($auth->verifyChallenge('654321'));
	}
}
