<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Auth\Challenge;
use Kirby\Auth\Challenges;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Email\Email;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Redirect;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Component;
use PHPUnit\Framework\Attributes\CoversClass;

class SwitchTestChallenge extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	public function create(): Pending|null
	{
		return null;
	}

	public function form(Pending $pending): Component
	{
		return new Component(component: 'k-test-form');
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return true;
	}
}

class SwitchTestChallenge2 extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	public function create(): Pending|null
	{
		return null;
	}

	public function form(Pending $pending): Component
	{
		return new Component(component: 'k-test2-form');
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return true;
	}
}

#[CoversClass(LoginRequestController::class)]
class LoginRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.LoginRequestController';

	protected static string $code;
	protected static string $password;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		static::$password = User::hashPassword('secret123');
		static::$code = User::hashPassword('123456');
	}

	public function setUp(): void
	{
		parent::setUp();
		Email::$debug = true;

		Challenges::$challenges['switch-test']  = SwitchTestChallenge::class;
		Challenges::$challenges['switch-test2'] = SwitchTestChallenge2::class;

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

	public function tearDown(): void
	{
		Email::$debug = false;
		unset(
			Challenges::$challenges['switch-test'],
			Challenges::$challenges['switch-test2']
		);
		parent::tearDown();
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

		$exception = null;

		try {
			$controller = new LoginRequestController('method', 'password');
			$controller->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('/panel/site', $exception->location());
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

		$controller = new LoginRequestController('method', 'password');
		$controller->load();
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

		$controller = new LoginRequestController('unknown', 'password');
		$controller->load();
	}

	public function testSwitch(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'api.csrf'   => 'test-csrf',
				'auth.debug' => true,
				'auth' => [
					'challenges' => ['switch-test', 'switch-test2']
				]
			],
			'request' => [
				'query' => [
					'csrf' => 'test-csrf'
				]
			],
			'users' => [
				[
					'email' => 'test@example.com',
					'role'  => 'admin'
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'switch-test');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 600);

		$exception = null;

		try {
			$controller = new LoginRequestController('switch', 'switch-test2');
			$controller->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('login/challenge/switch-test2', $exception->location());
		$this->assertSame('switch-test2', $session->get('kirby.challenge.type'));
	}

	public function testSwitchRequiresCsrf(): void
	{
		// switch goes through the same CSRF gate as the other types
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid CSRF token');

		$controller = new LoginRequestController('switch', 'switch-test2');
		$controller->load();
	}

	public function testSwitchWithoutPendingState(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf'
				]
			]
		]);

		// no challenge in session = not pending = nothing to switch from
		$exception = null;

		try {
			(new LoginRequestController('switch', 'email'))->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('/panel/login', $exception->location());
	}

	public function testSwitchWithoutName(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf'
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.timeout', time() + 600);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('No challenge type given');

		$controller = new LoginRequestController('switch');
		$controller->load();
	}

	public function testVerifySuccessfulRedirectsAfterLogin(): void
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
		$session->set('kirby.challenge.data', ['secret' => static::$code]);
		$session->set('kirby.challenge.timeout', time() + 600);

		$exception = null;

		try {
			$controller = new LoginRequestController('challenge', 'email');
			$controller->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('/panel/site', $exception->location());
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

		// No challenge session data = state is inactive
		// (challenge expired on a previous request)
		$exception = null;

		try {
			$controller = new LoginRequestController('challenge', 'email');
			$controller->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('/panel/login', $exception->location());
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
			$controller = new LoginRequestController('challenge', 'email');
			$controller->load();
		} catch (Redirect $e) {
			$exception = $e;
		}

		$this->assertInstanceOf(Redirect::class, $exception);
		$this->assertStringEndsWith('reset-password', $exception->location());
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

		$controller = new LoginRequestController('challenge', 'totp');
		$controller->load();
	}

	public function testVerifyRethrowsWhenCodeIsInvalid(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'csrf' => 'test-csrf',
					'code' => 'wrong-code',
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'email');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.data', ['secret' => static::$code]);
		$session->set('kirby.challenge.timeout', time() + 600);

		$this->expectException(PermissionException::class);
		$controller = new LoginRequestController('challenge', 'email');
		$controller->load();
	}
}
