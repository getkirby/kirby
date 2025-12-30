<?php

namespace Kirby\Cms;

use Kirby\Auth\Limits;
use Kirby\Auth\Methods;
use Kirby\Cms\Auth\Status;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Session\AutoSession;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(Auth::class)]
class AuthTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.Auth';

	protected Auth $auth;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'api' => [
					'basicAuth'     => true,
					'allowInsecure' => true
				],
				'auth' => [
					'debug' => false
				]
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => password_hash('springfield123', PASSWORD_DEFAULT)
				],
				[
					'email'    => 'homer@simpsons.com',
					'id'       => 'homer',
					'password' => $hash = password_hash('springfield123', PASSWORD_DEFAULT)
				],
				[
					'email'    => 'kirby@getkirby.com',
					'id'       => 'kirby',
					'password' => password_hash('somewhere-in-japan', PASSWORD_DEFAULT)
				],
			]
		]);
		Dir::make(static::TMP . '/site/accounts/homer');
		F::write(static::TMP . '/site/accounts/homer/.htpasswd', $hash);
		touch(static::TMP . '/site/accounts/homer/.htpasswd', 1337000000);

		$this->auth = $this->app->auth();
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
		App::destroy();
		$_GET = [];
	}

	public function testAuthenticate(): void
	{
		$status = $this->createStub(Status::class);
		$user   = $this->createStub(User::class);
		$methods = $this->createStub(Methods::class);
		$methods->method('authenticate')
			->willReturnCallback(function (string $type) use ($status, $user) {
				return match ($type) {
					'password' => $user,
					'code'     => $status
				};
			});

		$auth = new class ($methods, $status) extends Auth {
			public bool $didResetUser = false;

			public function __construct(
				protected Methods $methods,
				protected Status|null $status
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

	public function testImpersonate(): void
	{
		$this->assertNull($this->auth->user());

		$user = $this->auth->impersonate('kirby');
		$this->assertSame([
			'challenge' => null,
			'email'     => 'kirby@getkirby.com',
			'mode'      => null,
			'status'    => 'impersonated'
		], $this->auth->status()->toArray());
		$this->assertIsUser($user, $this->auth->user());
		$this->assertIsUser($user, $this->auth->currentUserFromImpersonation());
		$this->assertIsUser('kirby', $user);
		$this->assertSame('kirby@getkirby.com', $user->email());
		$this->assertSame('admin', $user->role()->name());
		$this->assertNull($this->auth->user(null, false));

		$user = $this->auth->impersonate('homer@simpsons.com');
		$this->assertSame([
			'challenge' => null,
			'email'     => 'homer@simpsons.com',
			'mode'      => null,
			'status'    => 'impersonated'
		], $this->auth->status()->toArray());
		$this->assertSame('homer@simpsons.com', $user->email());
		$this->assertIsUser($user, $this->auth->user());
		$this->assertIsUser($user, $this->auth->currentUserFromImpersonation());
		$this->assertNull($this->auth->user(null, false));

		$this->assertNull($this->auth->impersonate(null));
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
		$this->assertNull($this->auth->user());
		$this->assertNull($this->auth->currentUserFromImpersonation());
		$this->assertNull($this->auth->user(null, false));

		$this->auth->setUser($actual = $this->app->user('marge@simpsons.com'));
		$this->assertSame([
			'challenge' => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => null,
			'status'    => 'active'
		], $this->auth->status()->toArray());
		$this->assertSame('marge@simpsons.com', $this->auth->user()->email());
		$impersonated = $this->auth->impersonate('nobody');
		$this->assertSame([
			'challenge' => null,
			'email'     => 'nobody@getkirby.com',
			'mode'      => null,
			'status'    => 'impersonated'
		], $this->auth->status()->toArray());
		$this->assertSame($impersonated, $this->auth->user());
		$this->assertSame($impersonated, $this->auth->currentUserFromImpersonation());
		$this->assertSame('nobody', $impersonated->id());
		$this->assertSame('nobody@getkirby.com', $impersonated->email());
		$this->assertSame('nobody', $impersonated->role()->name());
		$this->assertSame($actual, $this->auth->user(null, false));

		$this->auth->logout();
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
		$this->assertNull($this->auth->impersonate());
		$this->assertNull($this->auth->user());
		$this->assertNull($this->auth->currentUserFromImpersonation());
		$this->assertNull($this->auth->user(null, false));
	}

	public function testImpersonateInvalidUser(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "lisa@simpsons.com" cannot be found');

		$this->auth->impersonate('lisa@simpsons.com');
	}

	public function testLimits(): void
	{
		$this->assertInstanceOf(Limits::class, $this->auth->limits());
	}

	public function testLogin(): void
	{
		// set the status cache
		$this->auth->status();

		$this->assertNull($this->app->user());

		$user = $this->auth->login('marge@simpsons.com', 'springfield123');
		$this->assertSame($this->app->user('marge@simpsons.com'), $user);

		$this->assertIsUser($user, $this->app->user());
		$this->assertSame(1800, $this->app->session()->timeout()); // not a long session

		$this->assertSame('marge@simpsons.com', $this->auth->status()->email());
	}

	public function testLoginLong(): void
	{
		// set the status cache
		$this->auth->status();

		$this->assertNull($this->app->user());

		$user = $this->auth->login('marge@simpsons.com', 'springfield123', true);
		$this->assertSame($this->app->user('marge@simpsons.com'), $user);

		$this->assertIsUser($user, $this->app->user());
		$this->assertFalse($this->app->session()->timeout()); // a long session

		$this->assertSame('marge@simpsons.com', $this->auth->status()->email());
	}

	public function testLoginInvalidUser(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->auth->login('lisa@simpsons.com', 'springfield123');
	}

	public function testLoginInvalidPassword(): void
	{
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
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
	}

	public function testLogoutPending(): void
	{
		$session = $this->app->session();

		$this->auth->createChallenge('marge@simpsons.com');

		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
		$this->assertSame('login', $session->get('kirby.challenge.mode'));

		$this->auth->logout();

		$this->assertNull($session->get('kirby.userId'));
		$this->assertNull($session->get('kirby.challenge.mode'));

		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
	}

	public function testMethods(): void
	{
		$this->assertInstanceOf(Methods::class, $this->auth->methods());
	}

	public function testTypeBasic1(): void
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

	public function testTypeBasic2(): void
	{
		// non-existing basic auth should
		// fall back to impersonation
		$this->auth->impersonate('kirby');

		$this->assertSame('impersonate', $this->auth->type());

		// auth object should have been accessed
		$this->assertTrue($this->app->response()->usesAuth());
	}

	public function testTypeBasic3(): void
	{
		// non-existing basic auth without
		// impersonation should fall back to session
		$this->assertSame('session', $this->auth->type());

		// auth object should have been accessed
		$this->assertTrue($this->app->response()->usesAuth());
	}

	public function testTypeBasic4(): void
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
		$app = $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth' => false
				]
			]
		]);

		$app->auth()->impersonate('kirby');

		$this->assertSame('impersonate', $app->auth()->type());
	}

	public function testTypeSession(): void
	{
		$app = $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth' => false
				]
			]
		]);

		$this->assertSame('session', $app->auth()->type());
	}

	public function testUserSession(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');

		$user = $this->auth->user();
		$this->assertSame('marge@simpsons.com', $user->email());

		$this->assertSame([
			'challenge' => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => null,
			'status'    => 'active'
		], $this->auth->status()->toArray());

		// impersonation is not set
		$this->assertNull($this->auth->currentUserFromImpersonation());

		// value is cached
		$session->set('kirby.userId', 'homer');
		$user = $this->auth->user();
		$this->assertSame('marge@simpsons.com', $user->email());
		$this->assertSame([
			'challenge' => null,
			'email'     => 'marge@simpsons.com',
			'mode'      => null,
			'status'    => 'active'
		], $this->auth->status()->toArray());
	}

	public function testUserSessionManualSession(): void
	{
		$session = (new AutoSession($this->app->root('sessions')))->createManually();
		$session->set('kirby.userId', 'homer');
		$session->set('kirby.loginTimestamp', 1337000000);

		$user = $this->auth->user($session);
		$this->assertSame('homer@simpsons.com', $user->email());
		$this->assertSame([
			'challenge' => null,
			'email'     => 'homer@simpsons.com',
			'mode'      => null,
			'status'    => 'active'
		], $this->auth->status()->toArray());
	}

	public function testUserSessionOldTimestamp(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'homer');
		$session->set('kirby.loginTimestamp', 1000000000);

		$this->assertNull($this->auth->user());
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());

		// user should be logged out completely
		$this->assertSame([], $session->data()->get());
	}

	public function testUserSessionNoTimestamp(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'homer');

		$this->assertNull($this->auth->user());
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'mode'      => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());

		// user should be logged out completely
		$this->assertSame([], $session->data()->get());
	}

	public function testUserBasicAuth(): void
	{
		$this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('homer@simpsons.com:springfield123')
			]
		]);

		$user = $this->auth->user();
		$this->assertSame('homer@simpsons.com', $user->email());

		$this->assertSame([
			'challenge' => null,
			'email'     => 'homer@simpsons.com',
			'mode'      => null,
			'status'    => 'active'
		], $this->auth->status()->toArray());
	}

	public function testUserBasicAuthInvalid1(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('homer@simpsons.com:invalid')
			]
		]);

		$this->auth->user();
	}

	public function testUserBasicAuthInvalid2(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('Invalid login');

		$this->app->clone([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('homer@simpsons.com:invalid')
			]
		]);

		try {
			$this->auth->user();
		} catch (Throwable) {
			// tested above, this check is for the second call
		}

		$this->auth->user();
	}
}
