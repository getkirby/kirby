<?php

namespace Kirby\Guards;

use Kirby\Cms\App;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserAbilities::class)]
class UserAbilitiesTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/../Api/Routes/fixtures';
	public const TMP = KIRBY_TMP_DIR . '/Guards.UserAbilities';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);
	}

	protected function abilities(string $email): UserAbilities
	{
		return new UserAbilities(
			model: $this->app->user($email),
			// mirrors `User::ensure()`, which the model guards use
			user: $this->app->user() ?? User::nobody()
		);
	}

	protected function createAvatar(string $email): void
	{
		$source = static::TMP . '/tmp-avatar.jpg';

		F::copy(
			source: static::FIXTURES . '/avatar.jpg',
			target: $source,
			force: true
		);

		$this->app->impersonate('kirby');
		$this->app->user($email)->createAvatar(
			source: $source,
			extension: 'jpg'
		);
	}

	public function testChangeRoleForAdminAsAdmin(): void
	{
		// add a second admin to make sure the target is not the last admin
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'another-admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('another-admin@getkirby.com');

		$this->assertNull($this->abilities('admin@getkirby.com')->ensure('changeRole'));
	}

	public function testChangeRoleForAdminAsEditor(): void
	{
		// add a second admin to make sure the target is not the last admin
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'another-admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeRole.demoteAdmin');

		$this->abilities('admin@getkirby.com')->ensure('changeRole');
	}

	public function testChangeRoleForEditor(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('changeRole'));
	}

	public function testChangeRoleForLastAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeRole.lastAdmin');

		$this->abilities('admin@getkirby.com')->ensure('changeRole');
	}

	public function testChangeRoleToAdminAsAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('changeRoleToAdmin'));
	}

	public function testChangeRoleToAdminAsEditor(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeRole.toAdmin');

		$this->abilities('editor@getkirby.com')->ensure('changeRoleToAdmin');
	}

	public function testChangeSecretForOtherUserAsAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('changeSecret'));
	}

	public function testChangeSecretForOtherUserAsEditor(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor'],
				['email' => 'another-editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeSecret');

		$this->abilities('another-editor@getkirby.com')->ensure('changeSecret');
	}

	public function testChangeSecretForSelf(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('changeSecret'));
	}

	public function testChangeSecretWithoutUser(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.changeSecret');

		$this->abilities('editor@getkirby.com')->ensure('changeSecret');
	}

	public function testCreateAdminAsAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertNull($this->abilities('admin@getkirby.com')->ensure('create'));
	}

	public function testCreateAdminAsEditor(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.create.admin');

		$this->abilities('admin@getkirby.com')->ensure('create');
	}

	public function testCreateAvatar(): void
	{
		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('createAvatar'));
	}

	public function testCreateAvatarWithExistingAvatar(): void
	{
		$this->createAvatar('editor@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.avatar.duplicate');

		$this->abilities('editor@getkirby.com')->ensure('createAvatar');
	}

	public function testCreateEditorAsEditor(): void
	{
		$this->app->impersonate('editor@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('create'));
	}

	public function testCreateFirstUser(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				['name' => 'admin']
			]
		]);

		$user = new User([
			'email' => 'admin@getkirby.com',
			'role'  => 'admin'
		]);

		$abilities = new UserAbilities(
			model: $user,
			user: $user
		);

		$this->assertNull($abilities->ensure('createFirstUser'));
	}

	public function testCreateFirstUserWithExistingUsers(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.create.first');

		$this->abilities('editor@getkirby.com')->ensure('createFirstUser');
	}

	public function testDeleteAdmin(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'another-admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$this->app->impersonate('admin@getkirby.com');

		$this->assertNull($this->abilities('admin@getkirby.com')->ensure('delete'));
	}

	public function testDeleteAvatar(): void
	{
		$this->createAvatar('editor@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('deleteAvatar'));
	}

	public function testDeleteAvatarWithoutAvatar(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.avatar.notFound');

		$this->abilities('editor@getkirby.com')->ensure('deleteAvatar');
	}

	public function testDeleteEditor(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('delete'));
	}

	public function testDeleteLastAdmin(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.delete.lastAdmin');

		$this->abilities('admin@getkirby.com')->ensure('delete');
	}

	public function testDeleteLastUser(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				['name' => 'editor']
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.delete.lastUser');

		$this->abilities('editor@getkirby.com')->ensure('delete');
	}

	public function testReplaceAvatar(): void
	{
		$this->createAvatar('editor@getkirby.com');

		$this->assertNull($this->abilities('editor@getkirby.com')->ensure('replaceAvatar'));
	}

	public function testReplaceAvatarWithoutAvatar(): void
	{
		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.user.avatar.notFound');

		$this->abilities('editor@getkirby.com')->ensure('replaceAvatar');
	}
}
