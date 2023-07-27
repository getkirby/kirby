<?php

namespace Kirby\Cms;

use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Session\AutoSession;
use Throwable;

/**
 * @coversDefaultClass \Kirby\Cms\Auth
 */
class AuthTest extends TestCase
{
	protected $app;
	protected $auth;
	protected $tmp;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp = __DIR__ . '/tmp'
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
				]
			]
		]);
		Dir::make($this->tmp . '/site/accounts/homer');
		F::write($this->tmp . '/site/accounts/homer/.htpasswd', $hash);
		touch($this->tmp . '/site/accounts/homer/.htpasswd', 1337000000);

		$this->auth = $this->app->auth();
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove($this->tmp);
		App::destroy();
	}

	/**
	 * @covers ::currentUserFromImpersonation
	 * @covers ::impersonate
	 * @covers ::status
	 * @covers ::user
	 */
	public function testImpersonate()
	{
		$this->assertNull($this->auth->user());

		$user = $this->auth->impersonate('kirby');
		$this->assertSame([
			'challenge' => null,
			'email'     => 'kirby@getkirby.com',
			'status'    => 'impersonated'
		], $this->auth->status()->toArray());
		$this->assertSame($user, $this->auth->user());
		$this->assertSame($user, $this->auth->currentUserFromImpersonation());
		$this->assertSame('kirby', $user->id());
		$this->assertSame('kirby@getkirby.com', $user->email());
		$this->assertSame('admin', $user->role()->name());
		$this->assertNull($this->auth->user(null, false));

		$user = $this->auth->impersonate('homer@simpsons.com');
		$this->assertSame([
			'challenge' => null,
			'email'     => 'homer@simpsons.com',
			'status'    => 'impersonated'
		], $this->auth->status()->toArray());
		$this->assertSame('homer@simpsons.com', $user->email());
		$this->assertSame($user, $this->auth->user());
		$this->assertSame($user, $this->auth->currentUserFromImpersonation());
		$this->assertNull($this->auth->user(null, false));

		$this->assertNull($this->auth->impersonate(null));
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
		$this->assertNull($this->auth->user());
		$this->assertNull($this->auth->currentUserFromImpersonation());
		$this->assertNull($this->auth->user(null, false));

		$this->auth->setUser($actual = $this->app->user('marge@simpsons.com'));
		$this->assertSame([
			'challenge' => null,
			'email'     => 'marge@simpsons.com',
			'status'    => 'active'
		], $this->auth->status()->toArray());
		$this->assertSame('marge@simpsons.com', $this->auth->user()->email());
		$impersonated = $this->auth->impersonate('nobody');
		$this->assertSame([
			'challenge' => null,
			'email'     => 'nobody@getkirby.com',
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
			'status'    => 'inactive'
		], $this->auth->status()->toArray());
		$this->assertNull($this->auth->impersonate());
		$this->assertNull($this->auth->user());
		$this->assertNull($this->auth->currentUserFromImpersonation());
		$this->assertNull($this->auth->user(null, false));
	}

	/**
	 * @covers ::impersonate
	 */
	public function testImpersonateInvalidUser()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "lisa@simpsons.com" cannot be found');

		$this->auth->impersonate('lisa@simpsons.com');
	}

	/**
	 * @covers ::type
	 */
	public function testTypeBasic1()
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

	/**
	 * @covers ::type
	 */
	public function testTypeBasic2()
	{
		// non-existing basic auth should
		// fall back to impersonation
		$this->auth->impersonate('kirby');

		$this->assertSame('impersonate', $this->auth->type());

		// auth object should have been accessed
		$this->assertTrue($this->app->response()->usesAuth());
	}

	/**
	 * @covers ::type
	 */
	public function testTypeBasic3()
	{
		// non-existing basic auth without
		// impersonation should fall back to session
		$this->assertSame('session', $this->auth->type());

		// auth object should have been accessed
		$this->assertTrue($this->app->response()->usesAuth());
	}

	/**
	 * @covers ::type
	 */
	public function testTypeBasic4()
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

	/**
	 * @covers ::type
	 */
	public function testTypeImpersonate()
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

	/**
	 * @covers ::type
	 */
	public function testTypeSession()
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

	/**
	 * @covers ::status
	 * @covers ::user
	 */
	public function testUserSession()
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');

		$user = $this->auth->user();
		$this->assertSame('marge@simpsons.com', $user->email());

		$this->assertSame([
			'challenge' => null,
			'email'     => 'marge@simpsons.com',
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
			'status'    => 'active'
		], $this->auth->status()->toArray());
	}

	/**
	 * @covers ::status
	 * @covers ::user
	 */
	public function testUserSessionManualSession()
	{
		$session = (new AutoSession($this->app->root('sessions')))->createManually();
		$session->set('kirby.userId', 'homer');
		$session->set('kirby.loginTimestamp', 1337000000);

		$user = $this->auth->user($session);
		$this->assertSame('homer@simpsons.com', $user->email());
		$this->assertSame([
			'challenge' => null,
			'email'     => 'homer@simpsons.com',
			'status'    => 'active'
		], $this->auth->status()->toArray());
	}

	/**
	 * @covers ::status
	 * @covers ::user
	 */
	public function testUserSessionOldTimestamp()
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'homer');
		$session->set('kirby.loginTimestamp', 1000000000);

		$this->assertNull($this->auth->user());
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());

		// user should be logged out completely
		$this->assertSame([], $session->data()->get());
	}

	/**
	 * @covers ::status
	 * @covers ::user
	 */
	public function testUserSessionNoTimestamp()
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'homer');

		$this->assertNull($this->auth->user());
		$this->assertSame([
			'challenge' => null,
			'email'     => null,
			'status'    => 'inactive'
		], $this->auth->status()->toArray());

		// user should be logged out completely
		$this->assertSame([], $session->data()->get());
	}

	/**
	 * @covers ::status
	 * @covers ::user
	 */
	public function testUserBasicAuth()
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
			'status'    => 'active'
		], $this->auth->status()->toArray());
	}

	/**
	 * @covers ::user
	 */
	public function testUserBasicAuthInvalid1()
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

	/**
	 * @covers ::user
	 */
	public function testUserBasicAuthInvalid2()
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
