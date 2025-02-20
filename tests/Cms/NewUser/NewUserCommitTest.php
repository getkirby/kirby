<?php

namespace Kirby\Cms;

use Kirby\Cms\NewUser as User;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(User::class)]
class NewUserCommitTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewUserCommit';

	public function testCommit(): void
	{
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'user.changeName:before' => [
					function (User $user, string $name) use ($phpunit) {
						$phpunit->assertSame('target', $name);
						$phpunit->assertSame('original', $user->name()->value());
						// altering $user which will be passed
						// to subsequent hook
						return new User(['name' => 'a']);
					},
					function (User $user, string $name) use ($phpunit) {
						$phpunit->assertSame('target', $name);
						// altered $user from previous hook
						$phpunit->assertSame('a', $user->name()->value());
						// altering $user which will be used
						// in the commit callback closure
						return new User(['name' => 'b']);
					}
				],
				'user.changeName:after' => [
					function (User $newUser, User $oldUser) use ($phpunit) {
						$phpunit->assertSame('original', $oldUser->name()->value());
						// modified $user from the commit callback closure
						$phpunit->assertSame('target', $newUser->name()->value());
						// altering $newUser which will be passed
						// to subsequent hook
						return new User(['name' => 'c']);
					},
					function (User $newUser, User $oldUser) use ($phpunit) {
						$phpunit->assertSame('original', $oldUser->name()->value());
						// altered $newUser from previous hook
						$phpunit->assertSame('c', $newUser->name()->value());
						// altering $newUser which will be the final result
						return new User(['name' => 'd']);
					}
				]
			]
		]);

		$app->impersonate('kirby');

		$user   = new User(['name' => 'original']);
		$class  = new ReflectionClass($user);
		$commit = $class->getMethod('commit');
		$result = $commit->invokeArgs($user, [
			'changeName',
			['user' => $user, 'name' => 'target'],
			function (User $user, string $name) use ($phpunit) {
				$phpunit->assertSame('target', $name);
				// altered $user from before hooks
				$phpunit->assertSame('b', $user->name()->value());
				return new User(['name' => $name]);
			}
		]);

		// altered result from last after hook
		$this->assertSame('d', $result->name()->value());
	}
}
