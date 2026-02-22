<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\RateLimitException;
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

	public function setUp(): void
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

	public function testValidatePasswordInvalidUser(): void
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

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('invalid@example.com', 'springfield123');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->limits()->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame('invalid@example.com', $this->failedEmail);
	}

	public function testValidatePasswordInvalidPassword(): void
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

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('marge@simpsons.com', 'wrong');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->limits()->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame(1, $this->auth->limits()->log()['by-email']['marge@simpsons.com']['trials']);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordBlocked(): void
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

		$this->app->visitor()->ip('10.2.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('marge@simpsons.com', 'springfield123');
		} catch (PermissionException $e) {
			$this->assertSame('Invalid login', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordDebugInvalidUser(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('lisa@simpsons.com', 'springfield123');
		} catch (UserNotFoundException $e) {
			$this->assertSame('The user "lisa@simpsons.com" cannot be found', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->limits()->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame('lisa@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordDebugInvalidPassword(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.3.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('marge@simpsons.com', 'invalid-password');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Wrong password', $e->getMessage());
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame(1, $this->auth->limits()->log()['by-ip']['85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da']['trials']);
		$this->assertSame(1, $this->auth->limits()->log()['by-email']['marge@simpsons.com']['trials']);
		$this->assertSame('marge@simpsons.com', $this->failedEmail);
	}

	public function testValidatePasswordDebugBlocked(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.2.123.234');

		$thrown = false;

		try {
			$this->auth->validatePassword('homer@simpsons.com', 'springfield123');
		} catch (RateLimitException $e) {
			$thrown = true;
		}

		$this->assertTrue($thrown);
		$this->assertSame('homer@simpsons.com', $this->failedEmail);
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
