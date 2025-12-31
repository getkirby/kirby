<?php

namespace Kirby\Cms;

use Kirby\Auth\Limits;
use Kirby\Auth\Methods;
use Kirby\Auth\Status;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

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

	public function testKirby(): void
	{
		$this->assertInstanceOf(App::class, $this->auth->kirby());
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

		$this->assertSame('marge@simpsons.com', $session->get('kirby.challenge.email'));
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

	public function testUser(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');
		$this->assertSame('marge@simpsons.com', $this->app->auth()->user()->email());
	}
}
