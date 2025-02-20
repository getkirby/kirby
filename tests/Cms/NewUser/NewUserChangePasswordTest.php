<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class NewUserChangePasswordTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserChangePassword';

	public function testChangePassword(): void
	{
		$user = new User(['email' => 'editor@domain.com']);
		$user = $user->changePassword('topsecret2018');

		$this->assertTrue($user->validatePassword('topsecret2018'));
	}

	public function testChangePasswordHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.changePassword:before' => function (User $user, $password) use ($phpunit, &$calls) {
					$phpunit->assertEmpty($user->password());
					$phpunit->assertSame('topsecret2018', $password);
					$calls++;
				},
				'user.changePassword:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertTrue($newUser->validatePassword('topsecret2018'));
					$phpunit->assertEmpty($oldUser->password());
					$calls += 2;
				},
				'user.login:before' => function () use (&$calls) {
					$calls += 4;
				},
				'user.login:after' => function () use (&$calls) {
					$calls += 8;
				},
			]
		]);

		$this->app->impersonate('kirby');

		$user = new User(['email' => 'editor@domain.com']);
		$user->changePassword('topsecret2018');

		$this->assertSame(3, $calls);
	}

	public function testChangePasswordHooksCurrentUser()
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'user.changePassword:before' => function (User $user, $password) use ($phpunit, &$calls) {
					$phpunit->assertEmpty($user->password());
					$phpunit->assertSame('topsecret2018', $password);
					$calls++;
				},
				'user.changePassword:after' => function (User $newUser, User $oldUser) use ($phpunit, &$calls) {
					$phpunit->assertTrue($newUser->validatePassword('topsecret2018'));
					$phpunit->assertEmpty($oldUser->password());
					$calls += 2;
				},
				'user.login:before' => function () use (&$calls) {
					$calls += 4;
				},
				'user.login:after' => function () use (&$calls) {
					$calls += 8;
				},
			]
		]);

		$user = new User([
			'email' => 'editor@domain.com',
			'role'  => 'admin'
		]);

		$this->app->users()->add($user);
		$this->app->impersonate('editor@domain.com');

		$user->changePassword('topsecret2018');
		$this->assertSame(15, $calls);

		$this->app->session()->destroy();
	}
}
