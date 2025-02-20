<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserAuthTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserAuth';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'id'    => 'testuser',
					'role'  => 'admin'
				]
			],
		]);
	}

	public function tearDown(): void
	{
		$this->app->session()->destroy();
		parent::tearDown();
	}

	public function testIsLoggedIn(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'a@getkirby.com'],
				['email' => 'b@getkirby.com']
			],
		]);

		$a = $this->app->user('a@getkirby.com');
		$b = $this->app->user('b@getkirby.com');

		$this->assertFalse($a->isLoggedIn());
		$this->assertFalse($b->isLoggedIn());

		$this->app->impersonate('a@getkirby.com');

		$this->assertTrue($a->isLoggedIn());
		$this->assertFalse($b->isLoggedIn());

		$this->app->impersonate('b@getkirby.com');

		$this->assertFalse($a->isLoggedIn());
		$this->assertTrue($b->isLoggedIn());
	}

	public function testLoginLogout(): void
	{
		$user = $this->app->user('test@getkirby.com');

		$this->assertNull($this->app->user());
		$user->loginPasswordless();
		$this->assertSame($user, $this->app->user());
		$user->logout();
		$this->assertNull($this->app->user());
	}

	public function testLoginLogoutHooks(): void
	{
		$calls         = 0;
		$phpunit       = $this;
		$logoutSession = false;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.login:before' => function ($user, $session) use ($phpunit, &$calls) {
					$phpunit->assertSame('test@getkirby.com', $user->email());
					$phpunit->assertSame($session, S::instance());

					$calls += 1;
				},
				'user.login:after' => function ($user, $session) use ($phpunit, &$calls) {
					$phpunit->assertSame('test@getkirby.com', $user->email());
					$phpunit->assertSame($session, S::instance());

					$calls += 2;
				},
				'user.logout:before' => function ($user, $session) use ($phpunit, &$calls) {
					$phpunit->assertSame('test@getkirby.com', $user->email());
					$phpunit->assertSame($session, S::instance());

					$calls += 4;
				},
				'user.logout:after' => function ($user, $session) use ($phpunit, &$calls, &$logoutSession) {
					$phpunit->assertSame('test@getkirby.com', $user->email());

					if ($logoutSession === true) {
						$phpunit->assertSame($session, S::instance());
						$phpunit->assertSame('value', S::instance()->get('some'));
					} else {
						$phpunit->assertNull($session);
					}

					$calls += 8;
				}
			]
		]);

		// without prepopulated session
		$user = $this->app->user('test@getkirby.com');
		$user->loginPasswordless();
		$user->logout();

		// with a session with another value
		S::instance()->set('some', 'value');
		$logoutSession = true;
		$user->loginPasswordless();
		$user->logout();

		// each hook needs to be called exactly twice
		$this->assertSame((1 + 2 + 4 + 8) * 2, $calls);
	}

	public function testLoginPasswordlessKirby(): void
	{
		$user = new User(['id' => 'kirby']);
		$this->expectException(PermissionException::class);
		$user->loginPasswordless();
	}

	public function testSessionData(): void
	{
		$user    = $this->app->user('test@getkirby.com');
		$session = $this->app->session();

		$this->assertSame([], $session->data()->get());
		$user->loginPasswordless();
		$this->assertSame(['kirby.userId' => 'testuser'], $session->data()->get());
		$user->logout();
		$this->assertSame([], $session->data()->get());
	}

	public function testSessionDataWithPassword(): void
	{
		F::write(static::TMP . '/site/accounts/testuser/.htpasswd', 'a very secure hash');

		$user    = $this->app->user('test@getkirby.com');
		$session = $this->app->session();

		$this->assertSame([], $session->data()->get());
		$user->loginPasswordless();
		$this->assertSame(['kirby.userId' => 'testuser', 'kirby.loginTimestamp' => 1337000000], $session->data()->get());
		$user->logout();
		$this->assertSame([], $session->data()->get());
	}
}
