<?php

namespace Kirby\Cms;

use Kirby\Tests\MockTime;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserChangeEmailTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.UserChangeEmail';

	protected function tearDown(): void
	{
		parent::tearDown();
		$this->app->session()->destroy();
		MockTime::reset();
	}

	public function testChangeEmail(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeEmail('another@domain.com');

		$this->assertSame('another@domain.com', $user->email());

		// verify the value stored on disk
		$user = $this->app->clone()->user($user->id());
		$this->assertSame('another@domain.com', $user->email());
	}

	public function testChangeEmailHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.changeEmail:before' => function (User $user, $email) use ($phpunit, &$calls) {
					$phpunit->assertSame('editor@domain.com', $user->email());
					$phpunit->assertSame('another@domain.com', $email);
					$calls++;
				},
				'user.changeEmail:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertSame('another@domain.com', $newUser->email());
					$phpunit->assertSame('editor@domain.com', $oldUser->email());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeEmail('another@domain.com');

		$this->assertSame(2, $calls);
	}

	public function testChangeEmailKeepsOtherSecrets(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeSecret('totp', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$user = $user->changeEmail('another@domain.com');

		// only the email challenge is tied to the address
		$this->assertSame(
			'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567',
			$this->app->clone()->user($user->id())->secret('totp')
		);
	}

	public function testChangeEmailKeepsUserLoggedIn(): void
	{
		$user = new User([
			'id'    => 'editor',
			'email' => 'editor@domain.com',
			'role'  => 'admin'
		]);

		$this->app->users()->add($user);

		$user = $user->changeSecret('email', true);
		$user->loginPasswordless();

		$session = $this->app->session();
		$token   = $session->token();

		MockTime::$time += 60;

		// resetting the email challenge opt-in renews the session
		$user = $user->changeEmail('another@domain.com');

		$this->assertSame('editor', $this->app->user()->id());
		$this->assertNotSame($token, $session->token());
		$this->assertSame(MockTime::$time, $session->get('kirby.loginTimestamp'));
	}

	public function testChangeEmailResetsEmailChallenge(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeSecret('email', true);

		$this->assertTrue($user->secret('email'));

		// the opt-in only proved the *old* address was reachable
		$user = $user->changeEmail('another@domain.com');
		$this->assertNull($user->secret('email'));

		// verify the value stored on disk
		$user = $this->app->clone()->user($user->id());
		$this->assertNull($user->secret('email'));
	}

	public function testChangeEmailWithUnicode(): void
	{
		$user = new User(['email' => 'editor@domain.com']);

		// with Unicode email
		$user = $user->changeEmail('test@exämple.com');
		$this->assertSame('test@exämple.com', $user->email());

		// verify the value stored on disk
		$app  = $this->app->clone();
		$user = $app->user($user->id());
		$this->assertSame('test@exämple.com', $user->email());

		$app->impersonate('kirby');

		// with Punycode email
		$user = $user->changeEmail('test@xn--tst-qla.com');
		$this->assertSame('test@täst.com', $user->email());

		// verify the value stored on disk
		$user = $this->app->clone()->user($user->id());
		$this->assertSame('test@täst.com', $user->email());
	}

	public function testChangeEmailWithUppercase(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changeEmail('ANOTHER@domain.com');

		$this->assertSame('another@domain.com', $user->email());

		// verify the value stored on disk
		$user = $this->app->clone()->user($user->id());
		$this->assertSame('another@domain.com', $user->email());
	}
}
