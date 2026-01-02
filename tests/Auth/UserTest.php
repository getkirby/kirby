<?php

namespace Kirby\Auth;

use Kirby\Cms\Auth;
use Kirby\Cms\User as CmsUser;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Request\Auth\BasicAuth;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.User';

	protected Auth $auth;
	protected User $user;

	public function setUp(): void
	{
		parent::setUp();

		$password = CmsUser::hashPassword('springfield123');

		$this->app = $this->app->clone([
			'options' => [
				'api' => [
					'basicAuth'     => true,
					'allowInsecure' => true
				]
			],
			'users' => [
				[
					'email'    => 'marge@simpsons.com',
					'id'       => 'marge',
					'password' => $password
				],
				[
					'email'    => 'homer@simpsons.com',
					'id'       => 'homer',
					'password' => $password
				],
				[
					'email'    => 'kirby@getkirby.com',
					'id'       => 'kirby',
					'password' => CmsUser::hashPassword('somewhere-in-japan')
				],
			]
		]);

		F::write(static::TMP . '/site/accounts/homer/.htpasswd', $password);
		touch(static::TMP . '/site/accounts/homer/.htpasswd', 1337000000);

		$this->auth = $this->app->auth();
		$this->user = new User($this->auth, $this->app);
	}

	public function testFlush(): void
	{
		$this->user->impersonate('kirby');
		$this->user->flush();

		$this->assertFalse($this->user->isImpersonated());
		$this->assertNull($this->user->get());
		$this->assertNull($this->user->fromImpersonation());
	}

	public function testFromBasicAuth(): void
	{
		$auth = new BasicAuth(base64_encode('marge@simpsons.com:springfield123'));

		$user = $this->user->fromBasicAuth($auth);

		$this->assertSame('marge@simpsons.com', $user->email());
	}

	public function testFromSession(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'marge');

		$user = $this->user->fromSession($session);
		$this->assertSame('marge@simpsons.com', $user->email());

		// value is cached
		$this->assertSame('marge@simpsons.com', $this->user->get($session)->email());
		$session->set('kirby.userId', 'homer');
		$this->assertSame('marge@simpsons.com', $this->user->get($session)->email());
	}

	public function testFromSessionInvalidated(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'homer');
		$session->set('kirby.loginTimestamp', 1);

		$this->assertNull($this->user->fromSession($session));
	}

	public function testFromSessionMissingUser(): void
	{
		$session = $this->app->session();
		$session->set('kirby.userId', 'lisa');

		$this->assertNull($this->user->fromSession($session));
	}

	public function testImpersonate(): void
	{
		$this->assertFalse($this->user->isImpersonated());
		$this->assertNull($this->user->fromImpersonation());

		$impersonated = $this->user->impersonate('kirby');
		$this->assertSame('kirby@getkirby.com', $impersonated->email());
		$this->assertTrue($this->user->isImpersonated());
		$this->assertSame($impersonated, $this->user->fromImpersonation());
		$this->assertSame($impersonated, $this->user->get());
		$this->assertNull($this->user->get(null, false));

		$actual = $this->user->impersonate('homer@simpsons.com');
		$this->assertSame('homer@simpsons.com', $actual->email());
		$this->assertSame($actual, $this->user->get());
		$this->assertNull($this->user->get(null, false));

		$this->assertNull($this->user->impersonate(null));
		$this->assertFalse($this->user->isImpersonated());
	}

	public function testImpersonateInvalidUser(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "lisa@simpsons.com" cannot be found');

		$this->user->impersonate('lisa@simpsons.com');
	}

	public function testSetResetsImpersonation(): void
	{
		$this->user->impersonate('kirby');
		$this->assertTrue($this->user->isImpersonated());

		$user = $this->app->user('marge@simpsons.com');
		$this->user->set($user);

		$this->assertFalse($this->user->isImpersonated());
		$this->assertSame($user, $this->user->get());
	}
}
