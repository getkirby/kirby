<?php

namespace Kirby\Auth;

use Exception;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Exception\UserNotFoundException;
use Kirby\Filesystem\F;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Auth';

	public string|null $failedEmail = null;

	protected static string $passwordA;
	protected static string $passwordB;

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		static::$passwordA = User::hashPassword('springfield123');
		static::$passwordB = User::hashPassword('somewhere-in-japan');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$self = $this;

		$this->app = $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth'     => true,
					'allowInsecure' => true
				],
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => self::$passwordA
				],
				[
					'email'    => 'homer@simpsons.com',
					'id'       => 'homer',
					'password' => self::$passwordA
				],
				[
					'email'    => 'kirby@getkirby.com',
					'id'       => 'kirby',
					'password' => self::$passwordB
				],
				[
					'email'    => 'test@exämple.com',
					'password' => self::$passwordA
				]
			],
			'hooks' => [
				'user.login:failed' => function ($email) use ($self) {
					$self->failedEmail = $email;
				}
			]
		]);

		F::write(static::TMP . '/site/accounts/marge/.htpasswd', self::$passwordA);
		F::write(static::TMP . '/site/accounts/homer/.htpasswd', self::$passwordA);

		$this->auth = $this->app->auth();
	}

	public function testAuthenticate(): void
	{
		$status  = $this->createStub(Status::class);
		$user    = $this->createStub(User::class);
		$methods = $this->createStub(Methods::class);
		$methods->method('authenticate')
			->willReturnCallback(function (string $type) use ($status, $user) {
				return match ($type) {
					'password' => $user,
					'code'     => $status
				};
			});

		$auth = new class ($methods) extends Auth {
			public bool $didResetUser = false;

			public function __construct(
				protected Methods $methods
			) {
			}

			public function methods(): Methods
			{
				return $this->methods;
			}

			public function setUser(User $user): void
			{
				$this->didResetUser = true;
			}
		};

		$result = $auth->authenticate('code', 'lisa@simpson.de');
		$this->assertSame($status, $result);
		$this->assertFalse($auth->didResetUser);

		$result = $auth->authenticate('password', 'lisa@simpson.de');
		$this->assertSame($user, $result);
		$this->assertTrue($auth->didResetUser);
	}

	public function testChallenges(): void
	{
		$this->assertInstanceOf(Challenges::class, $this->auth->challenges());
	}

	public function testCsrf(): void
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');
		$_GET = [];
		$this->assertFalse($this->auth->csrf());
	}

	public function testCsrfFromSession(): void
	{
		$this->app->session()->set('kirby.csrf', 'session-csrf');
		$_GET = ['csrf' => 'session-csrf'];
		$this->assertSame('session-csrf', $this->auth->csrfFromSession());
	}

	public function testCurrentUserFromImpersonation(): void
	{
		$this->auth->impersonate('marge');
		$user = $this->auth->currentUserFromImpersonation();
		$this->assertSame('marge@simpsons.com', $user->email());
	}

	public function testCurrentUserFromSession(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');
		$user = $this->app->user('marge@simpsons.com');
		$loginTimestamp = $user->passwordTimestamp() + 1;
		$session->set('kirby.loginTimestamp', $loginTimestamp);

		$user = $this->auth->currentUserFromSession($session);
		$this->assertSame('marge@simpsons.com', $user->email());
	}

	public function testCurrentUserFromBasicAuth(): void
	{
		$data = base64_encode('marge@simpsons.com:springfield123');
		$auth = new BasicAuth($data);
		$user = $this->auth->currentUserFromBasicAuth($auth);
		$this->assertSame('marge@simpsons.com', $user->email());
	}

	public function testGuard(): void
	{
		// a successful attempt returns its result untouched
		// and records no failure
		$result = $this->auth->guard(
			'marge@simpsons.com',
			fn () => 'success'
		);

		$this->assertSame('success', $result);
		$this->assertNull($this->failedEmail);
		$this->assertFileDoesNotExist(static::TMP . '/site/accounts/.logins');
	}

	public function testGuardEnforcesRateLimit(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		// IP 10.2 (10 trials) is already blocked
		$this->app->visitor()->ip('10.2.123.234');

		$ran    = false;
		$thrown = false;

		try {
			$this->auth->guard(
				'marge@simpsons.com',
				function () use (&$ran) {
					$ran = true;
					return 'should never run';
				},
				new PermissionException(message: 'hidden fallback')
			);

		} catch (PermissionException $e) {
			$this->assertSame('hidden fallback', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);

		// the attempt is short-circuited before it can run
		$this->assertFalse($ran);

		// the pre-existing rate-limit hit is not tracked twice
		$log = $this->auth->limits()->log();
		$this->assertSame(10, $log['by-ip']['38f0a08519792a17e18a251008f3a116977907f921b0b287c8']['trials']);
	}

	public function testGuardKeepsTrackingError(): void
	{
		// if tracking the failed attempt fails itself (here via a
		// throwing user.login:failed hook) `::guard()` surfaces that
		// tracking error instead of the original failure
		$this->app = $this->app->clone([
			'hooks' => [
				'user.login:failed' => function () {
					throw new Exception('tracking failed');
				}
			]
		]);
		$this->auth = $this->app->auth();

		$this->app->visitor()->ip('10.3.123.234');

		// debug mode (default) rethrows whatever error guard keeps
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('tracking failed');

		$this->auth->guard(
			'marge@simpsons.com',
			fn () => throw new InvalidArgumentException(message: 'real reason')
		);
	}

	public function testGuardRethrowsInDebugMode(): void
	{
		// auth.debug defaults to true in the test setup, so the
		// real error is surfaced instead of the generic fallback
		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->guard(
				'marge@simpsons.com',
				fn () => throw new InvalidArgumentException(message: 'real reason'),
				new PermissionException(message: 'hidden fallback')
			);
		} catch (InvalidArgumentException $e) {
			$this->assertSame('real reason', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
	}

	public function testGuardReturnsNullWithoutFallback(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		$this->app->visitor()->ip('10.3.123.234');

		// without a fallback the hidden failure is swallowed
		// and guard() resolves to null
		$result = $this->auth->guard(
			'marge@simpsons.com',
			fn () => throw new InvalidArgumentException(message: 'real reason')
		);

		$this->assertNull($result);

		// the failed attempt is still tracked
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testGuardTracksAndHidesFailure(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->guard(
				'marge@simpsons.com',
				fn () => throw new InvalidArgumentException(message: 'real reason'),
				new PermissionException(message: 'hidden fallback')
			);
		} catch (PermissionException $e) {
			// the real reason is hidden behind the generic fallback
			$this->assertSame('hidden fallback', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);

		// the failed attempt is tracked by IP and email
		// and the user.login:failed hook has fired
		$log = $this->auth->limits()->log();
		$this->assertSame(1, $log['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame(1, $log['by-email']['marge@simpsons.com']['trials']);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testGuardWithoutEmail(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		// IP 10.2 (10 trials) is blocked, so an
		// identity-unknown attempt is throttled by IP alone
		$this->app->visitor()->ip('10.2.123.234');

		// prime to prove the hook fires with a null email
		$this->failedEmail = 'foo';

		$ran    = false;
		$thrown = false;

		try {
			$this->auth->guard(
				null,
				function () use (&$ran) {
					$ran = true;
					return 'should never run';
				},
				new PermissionException(message: 'hidden fallback')
			);
		} catch (PermissionException) {
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertFalse($ran);
		$this->assertNull($this->failedEmail);
	}

	public function testImpersonate(): void
	{
		$this->auth->status();
		$user = $this->auth->impersonate('kirby');

		$this->assertInstanceOf(User::class, $user);
		$this->assertSame('kirby@getkirby.com', $user->email());
		$this->assertTrue($this->auth->status()->is(State::Impersonated));
	}

	public function testKirby(): void
	{
		$this->assertSame($this->app, $this->auth->kirby());
	}

	public function testLimits(): void
	{
		$this->assertInstanceOf(Limits::class, $this->auth->limits());
	}

	public function testLogin(): void
	{
		$this->auth->status();

		$this->assertNull($this->app->user());

		$user = $this->auth->login('marge@simpsons.com', 'springfield123');
		$this->assertSame($this->app->user('marge@simpsons.com'), $user);

		$this->assertInstanceOf(User::class, $this->app->user());
		$this->assertSame(1800, $this->app->session()->timeout());
		$this->assertSame('marge@simpsons.com', $this->auth->status()->email());
	}

	public function testLoginLong(): void
	{
		$this->auth->status();

		$user = $this->auth->login('marge@simpsons.com', 'springfield123', true);
		$this->assertSame($this->app->user('marge@simpsons.com'), $user);

		$this->assertInstanceOf(User::class, $this->app->user());
		$this->assertFalse($this->app->session()->timeout());
		$this->assertSame('marge@simpsons.com', $this->auth->status()->email());
	}

	public function testLoginInvalidUser(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->auth->login('lisa@simpsons.com', 'springfield123');
	}

	public function testLoginInvalidPassword(): void
	{
		$this->app  = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->auth->login('marge@simpsons.com', 'springfield456');
	}

	public function testLoginKirby(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage(
			'The almighty user "kirby" cannot be used for login, ' .
			'only for raising permissions in code via `$kirby->impersonate()`'
		);

		$this->auth->login('kirby@getkirby.com', 'somewhere-in-japan');
	}

	public function testLogoutActive(): void
	{
		$session = $this->app->session();

		$this->app->user('marge@simpsons.com')->loginPasswordless();
		$this->app->impersonate('homer@simpsons.com');

		$this->assertSame('marge', $session->get('kirby.userId'));

		$this->auth->logout();

		$this->assertNull($session->get('kirby.userId'));

		$this->assertSame([
			'challenge' => null,
			'data'      => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
	}

	public function testLogoutPending(): void
	{
		$session = $this->app->session();

		$this->auth->createChallenge('marge@simpsons.com');

		$email = $session->get('kirby.challenge.email');
		$this->assertSame('marge@simpsons.com', $email);
		$this->assertSame('login', $session->get('kirby.challenge.mode'));

		$this->auth->logout();

		$this->assertNull($session->get('kirby.userId'));
		$this->assertNull($session->get('kirby.challenge.mode'));

		$this->assertSame([
			'challenge' => null,
			'data'      => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
	}

	public function testMethods(): void
	{
		$this->assertInstanceOf(Methods::class, $this->auth->methods());
	}

	public function testPasswords(): void
	{
		$this->assertInstanceOf(Passwords::class, $this->auth->passwords());

		// reads the `auth.passwords` option
		$app = $this->app->clone([
			'options' => ['auth' => ['passwords' => ['minlength' => 12]]]
		]);

		$this->assertSame(12, $app->auth()->passwords()->minlength());
	}

	public function testSessionHelperWithOptions(): void
	{
		$session = $this->auth->session(['detect' => true]);
		$this->assertInstanceOf(Session::class, $session);
	}

	public function testSessionHelperWithObject(): void
	{
		$session = $this->app->session();
		$this->assertSame($session, $this->auth->session($session));
	}

	public function testStatus(): void
	{
		$user = $this->app->user('marge@simpsons.com');
		$user->loginPasswordless();

		$status = $this->auth->status();
		$this->assertInstanceOf(Status::class, $status);
		$this->assertSame('marge@simpsons.com', $status->email());
		$this->assertSame(State::Active, $status->state());
	}

	public function testTypeBasicPreferredOverImpersonation(): void
	{
		$app = $this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('testuser:testpass')
			]
		]);

		// existing basic auth should be preferred
		// over impersonation
		$app->auth()->impersonate('kirby');
		$this->assertSame('basic', $app->auth()->type());

		// auth object should have been accessed
		$this->assertTrue($app->response()->usesAuth());
	}

	public function testTypeBasicFallsBackToImpersonation(): void
	{
		// non-existing basic auth should
		// fall back to impersonation
		$this->auth->impersonate('kirby');
		$this->assertSame('impersonate', $this->auth->type());

		// auth object should have been accessed
		$this->assertTrue($this->app->response()->usesAuth());
	}

	public function testTypeBasicFallsBackToSession(): void
	{
		// non-existing basic auth without
		// impersonation should fall back to session
		$this->assertSame('session', $this->auth->type());

		// auth object should have been accessed
		$this->assertTrue($this->app->response()->usesAuth());
	}

	public function testTypeBasicDisabledOption(): void
	{
		$app = $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth' => false
				]
			],
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('testuser:testpass')
			]
		]);

		// disabled option should fall back to session
		$this->assertSame('session', $app->auth()->type());

		// auth object should *not* have been accessed
		$this->assertFalse($app->response()->usesAuth());
	}

	public function testTypeBasicWithInvalidConfiguration(): void
	{
		// when api.basicAuth is enabled and the request carries a Basic
		// header but a gating condition fails (e.g. no HTTPS),
		// type() must still return 'basic' so the basic-auth path
		// surfaces a precise PermissionException rather than silently
		// falling back to session auth

		$app = $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth'     => true,
					'allowInsecure' => false
				]
			],
			'server' => [
				'HTTPS'              => '',
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('marge@simpsons.com:springfield123')
			]
		]);

		$this->assertSame('basic', $app->auth()->type());

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Basic authentication is only allowed over HTTPS');
		$app->auth()->currentUserFromBasicAuth();
	}

	public function testTypeImpersonate(): void
	{
		$this->app->auth()->impersonate('kirby');
		$this->assertSame('impersonate', $this->app->auth()->type());
	}

	public function testTypeSession(): void
	{
		$this->assertSame('session', $this->auth->type());
		$this->assertTrue($this->app->response()->usesAuth());
	}

	public function testUserFromSession(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');
		$loginTimestamp = $this->app->user('marge@simpsons.com')->passwordTimestamp() + 1;
		$session->set('kirby.loginTimestamp', $loginTimestamp);
		$this->assertSame('marge@simpsons.com', $this->auth->user($session)->email());
	}

	public function testValidatePassword(): void
	{
		$user = $this->auth->validatePassword('marge@simpsons.com', 'springfield123');
		$this->assertSame('marge@simpsons.com', $user->email());
		$this->assertNull($this->failedEmail);
	}

	public function testValidatePasswordInvalidPassword(): void
	{
		$this->app->visitor()->ip('10.3.123.234');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Wrong password');

		$this->auth->validatePassword('marge@simpsons.com', 'invalid-password');
	}

	public function testValidatePasswordInvalidUser(): void
	{
		$this->app->visitor()->ip('10.3.123.234');

		$this->expectException(UserNotFoundException::class);
		$this->expectExceptionMessage('The user "lisa@simpsons.com" cannot be found');

		$this->auth->validatePassword('lisa@simpsons.com', 'springfield123');
	}

	public function testValidatePasswordIsGuarded(): void
	{
		// validatePassword() routes failures through Auth::guard(),
		// so in production the real error is hidden and the attempt
		// is tracked
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['debug' => false]
			]
		]);
		$this->auth = $this->app->auth();

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('marge@simpsons.com', 'wrong');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->limits()->log()['by-email']['marge@simpsons.com']['trials']);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordWithUnicodeEmail(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.3.123.234');

		$user = $this->auth->validatePassword('test@exämple.com', 'springfield123');

		$this->assertIsUser($user);
		$this->assertSame('test@exämple.com', $user->email());
	}

	public function testValidatePasswordWithPunycodeEmail(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.3.123.234');
		$user = $this->auth->validatePassword('test@xn--exmple-cua.com', 'springfield123');

		$this->assertIsUser($user);
		$this->assertSame('test@exämple.com', $user->email());
	}
}
