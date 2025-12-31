<?php

namespace Kirby\Auth;

use Kirby\Cms\User;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Auth::class)]
class AuthTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Auth';

	public function setUp(): void
	{
		parent::setUp();

		$margePassword = password_hash('springfield123', PASSWORD_DEFAULT);
		$homerPassword = password_hash('springfield123', PASSWORD_DEFAULT);

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
					'password' => $margePassword
				],
				[
					'email'    => 'homer@simpsons.com',
					'id'       => 'homer',
					'password' => $homerPassword
				],
				[
					'email'    => 'kirby@getkirby.com',
					'id'       => 'kirby',
					'password' => password_hash('somewhere-in-japan', PASSWORD_DEFAULT)
				]
			]
		]);

		F::write(static::TMP . '/site/accounts/marge/.htpasswd', $margePassword);
		F::write(static::TMP . '/site/accounts/homer/.htpasswd', $homerPassword);

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
		$this->assertSame('marge@simpsons.com', $this->auth->currentUserFromImpersonation()?->email());
	}

	public function testCurrentUserFromSession(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');
		$loginTimestamp = $this->app->user('marge@simpsons.com')->passwordTimestamp() + 1;
		$session->set('kirby.loginTimestamp', $loginTimestamp);

		$this->assertSame('marge@simpsons.com', $this->auth->currentUserFromSession($session)?->email());
	}

	public function testCurrentUserFromBasicAuth(): void
	{
		$auth = new BasicAuth(base64_encode('marge@simpsons.com:springfield123'));
		$this->assertSame('marge@simpsons.com', $this->auth->currentUserFromBasicAuth($auth)?->email());
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
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->auth->login('lisa@simpsons.com', 'springfield123');
	}

	public function testLoginInvalidPassword(): void
	{
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
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
	}

	public function testTypeBasicPreferredOverImpersonation(): void
	{
		$app = $this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('testuser:testpass')
			]
		]);

		$app->auth()->impersonate('kirby');

		$this->assertSame('basic', $app->auth()->type());
		$this->assertTrue($app->response()->usesAuth());
	}

	public function testTypeBasicFallsBackToImpersonation(): void
	{
		$this->auth->impersonate('kirby');
		$this->assertSame('impersonate', $this->auth->type());
		$this->assertTrue($this->app->response()->usesAuth());
	}

	public function testTypeSession(): void
	{
		$this->assertSame('session', $this->auth->type());
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

		$this->assertSame('session', $app->auth()->type());
		$this->assertFalse($app->response()->usesAuth());
	}

	public function testUserFromSession(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');
		$loginTimestamp = $this->app->user('marge@simpsons.com')->passwordTimestamp() + 1;
		$session->set('kirby.loginTimestamp', $loginTimestamp);

		$this->assertSame('marge@simpsons.com', $this->auth->user($session)?->email());
	}

	public function testValidatePassword(): void
	{
		$this->assertSame(
			'marge@simpsons.com',
			$this->auth->validatePassword('marge@simpsons.com', 'springfield123')?->email()
		);
	}

	public function testValidatePasswordInvalidUser(): void
	{
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->auth->validatePassword('invalid@example.com', 'springfield123');
	}

	public function testValidatePasswordInvalidPassword(): void
	{
		$this->app  = $this->app->clone(['options' => ['auth' => ['debug' => false]]]);
		$this->auth = $this->app->auth();

		$this->expectException(PermissionException::class);
		$this->auth->validatePassword('marge@simpsons.com', 'wrong');
	}

	public function testLogout(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');
		$session->set('kirby.challenge.email', 'marge@simpsons.com');
		$session->set('kirby.challenge.mode', 'login');
		$session->set('kirby.challenge.type', 'email');

		$this->auth->logout();

		$this->assertNull($session->get('kirby.userId'));
		$this->assertNull($session->get('kirby.challenge.email'));
		$this->assertNull($session->get('kirby.challenge.mode'));
		$this->assertNull($session->get('kirby.challenge.type'));
		$this->assertSame('inactive', $this->auth->status()->state()->value);
	}
}
