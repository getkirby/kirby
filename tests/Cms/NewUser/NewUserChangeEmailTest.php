<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserChangeEmailTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserChangeEmailTest';

	public function testChangeEmail(): void
	{
		$user = new User(['email' =>'editor@domain.com']);
		$user = $user->changeEmail('another@domain.com');

		$this->assertSame('another@domain.com', $user->email());

		// verify the value stored on disk
		$user = $this->app->clone()->user($user->id());
		$this->assertSame('another@domain.com', $user->email());
	}

	public function testChangeEmailWithUnicode(): void
	{
		$user = new User(['email' =>'editor@domain.com']);

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
		$user = new User(['email' =>'editor@domain.com']);
		$user = $user->changeEmail('ANOTHER@domain.com');

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
}
