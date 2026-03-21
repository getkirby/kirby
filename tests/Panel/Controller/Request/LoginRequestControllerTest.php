<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\User;
use Kirby\Email\Email;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Redirect;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginRequestController::class)]
class LoginRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.LoginRequestController';

	protected static string $password;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		static::$password = User::hashPassword('secret123');
	}

	public function tearDown(): void
	{
		Email::$debug = false;
		parent::tearDown();
	}

	public function setUp(): void
	{
		parent::setUp();
		Email::$debug = true;

		$this->app = $this->app->clone([
			'server' => [
				'SERVER_NAME' => 'getkirby.com',
			],
			'options' => [
				'api.csrf'   => 'test-csrf',
				'auth.debug' => true,
			],
			'users' => [
				[
					'email'    => 'test@example.com',
					'role'     => 'admin',
					'password' => static::$password
				]
			]
		]);
	}

	public function testAuthenticateRedirectsAfterLogin(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf'     => 'test-csrf',
					'email'    => 'test@example.com',
					'password' => 'secret123',
				]
			]
		]);

		$this->expectException(Redirect::class);
		(new LoginRequestController('method', 'password'))->load();
	}

	public function testAuthenticateRedirectsToChallenge(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf'     => 'test-csrf',
				'auth.debug'   => true,
				'auth.methods' => ['password' => ['2fa' => true]],
			],
			'request' => [
				'query' => [
					'csrf'     => 'test-csrf',
					'email'    => 'test@example.com',
					'password' => 'secret123',
				]
			]
		]);

		$this->expectException(Redirect::class);
		(new LoginRequestController('method', 'password'))->load();
	}

	public function testLoadInvalidCsrf(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'email'    => 'test@example.com',
					'password' => 'secret123',
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid CSRF token');

		(new LoginRequestController('method', 'password'))->load();
	}

	public function testLoadUnknownType(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf'
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid login route type "unknown"');

		(new LoginRequestController('unknown', 'password'))->load();
	}

	public function testVerifyDestroyedChallengeRedirectsToLogin(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf',
					'code' => '123456',
				]
			]
		]);

		// No challenge session data → state is Inactive (challenge expired on a previous request)
		$exception = null;

		try {
			(new LoginRequestController('challenge', 'email'))->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('/panel/login', $exception->location());
	}

	public function testVerifyInactiveChallenge(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf',
					'code' => '123456',
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 600);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Login challenge "totp" is not active');

		(new LoginRequestController('challenge', 'totp'))->load();
	}

	public function testVerifyRedirectsAfterLogin(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf',
					'code' => '123456',
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.data', [
			'secret' => password_hash('123456', PASSWORD_DEFAULT)
		]);
		$session->set('kirby.challenge.timeout', time() + 600);

		$this->expectException(Redirect::class);
		(new LoginRequestController('challenge', 'email'))->load();
	}

	public function testVerifyRedirectsToPasswordReset(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf',
					'code' => '654321',
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.mode', 'password-reset');
		$session->set('kirby.challenge.data', [
			'secret' => password_hash('654321', PASSWORD_DEFAULT)
		]);
		$session->set('kirby.challenge.timeout', time() + 600);

		$exception = null;

		try {
			(new LoginRequestController('challenge', 'email'))->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('reset-password', $exception->location());
	}
}
