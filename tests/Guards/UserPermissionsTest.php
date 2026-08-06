<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Role;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserPermissions::class)]
class UserPermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.UserPermissions';

	protected function permissions(
		string $email = 'admin@getkirby.com',
		array|bool $permissions = []
	): UserPermissions {
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				[
					'name'        => 'editor',
					'permissions' => [
						'user'  => $permissions,
						'users' => $permissions
					]
				]
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		return new UserPermissions(
			model: $this->app->user($email),
			user: $this->app->user()
		);
	}

	public function testCategory(): void
	{
		$this->assertSame('users', $this->permissions()->category());
	}

	public function testCategoryForCurrentUser(): void
	{
		$permissions = $this->permissions(email: 'editor@getkirby.com');

		$this->assertSame('user', $permissions->category());
	}

	public function testChangeEmail(): void
	{
		$this->assertNull($this->permissions()->ensure('changeEmail'));
	}

	public function testChangeEmailWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changeEmail' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeEmail.permission');

		$permissions->ensure('changeEmail');
	}

	public function testChangeLanguage(): void
	{
		$this->assertNull($this->permissions()->ensure('changeLanguage'));
	}

	public function testChangeLanguageWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changeLanguage' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeLanguage.permission');

		$permissions->ensure('changeLanguage');
	}

	public function testChangeName(): void
	{
		$this->assertNull($this->permissions()->ensure('changeName'));
	}

	public function testChangeNameWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changeName' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeName.permission');

		$permissions->ensure('changeName');
	}

	public function testChangePassword(): void
	{
		$this->assertNull($this->permissions()->ensure('changePassword'));
	}

	public function testChangePasswordWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changePassword' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changePassword.permission');

		$permissions->ensure('changePassword');
	}

	public function testChangeRole(): void
	{
		$this->assertNull($this->permissions()->ensure('changeRole'));
	}

	public function testChangeRoleToAdmin(): void
	{
		$this->assertNull($this->permissions()->ensure('changeRoleToAdmin'));
	}

	public function testChangeRoleToAdminWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changeRole' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeRole.permission');

		$permissions->ensure('changeRoleToAdmin');
	}

	public function testChangeRoleWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changeRole' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeRole.permission');

		$permissions->ensure('changeRole');
	}

	public function testChangeSecret(): void
	{
		$this->assertNull($this->permissions()->ensure('changeSecret'));
	}

	public function testChangeSecretWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['changeSecret' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.changeSecret.permission');

		$permissions->ensure('changeSecret');
	}

	public function testCreate(): void
	{
		$this->assertNull($this->permissions()->ensure('create'));
	}

	public function testCreateAvatar(): void
	{
		$this->assertNull($this->permissions()->ensure('createAvatar'));
	}

	public function testCreateAvatarWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['createAvatar' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.createAvatar.permission');

		$permissions->ensure('createAvatar');
	}

	public function testCreateAvatarWithoutUpdatePermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['update' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.update.permission');

		$permissions->ensure('createAvatar');
	}

	public function testCreateFirstUser(): void
	{
		$this->assertNull($this->permissions()->ensure('createFirstUser'));
	}

	public function testCreateWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['create' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.create.permission');

		$permissions->ensure('create');
	}

	public function testDelete(): void
	{
		$this->assertNull($this->permissions()->ensure('delete'));
	}

	public function testDeleteAvatar(): void
	{
		$this->assertNull($this->permissions()->ensure('deleteAvatar'));
	}

	public function testDeleteAvatarWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['deleteAvatar' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.deleteAvatar.permission');

		$permissions->ensure('deleteAvatar');
	}

	public function testDeleteAvatarWithoutUpdatePermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['update' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.update.permission');

		$permissions->ensure('deleteAvatar');
	}

	public function testDeleteWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['delete' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.delete.permission');

		$permissions->ensure('delete');
	}

	public function testError(): void
	{
		$permissions = $this->permissions();

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.test.permission');

		$permissions->error('test');
	}

	public function testReplaceAvatar(): void
	{
		$this->assertNull($this->permissions()->ensure('replaceAvatar'));
	}

	public function testReplaceAvatarWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['replaceAvatar' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.replaceAvatar.permission');

		$permissions->ensure('replaceAvatar');
	}

	public function testReplaceAvatarWithoutUpdatePermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['update' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.update.permission');

		$permissions->ensure('replaceAvatar');
	}

	public function testSettingForRoleWithDynamicCategory(): void
	{
		$this->permissions();

		$role = new Role([
			'name'        => 'editor',
			'permissions' => [
				'user'  => ['changeEmail' => true],
				'users' => ['changeEmail' => false]
			]
		]);

		// the category switches between `user` and `users`
		// depending on whether the model is the current user,
		// which changes the resolved rule
		$forSelf = new UserPermissions(
			model: $this->app->user('editor@getkirby.com'),
			user: $this->app->user()
		);

		$forOther = new UserPermissions(
			model: $this->app->user('admin@getkirby.com'),
			user: $this->app->user()
		);

		$this->assertTrue($forSelf->settingForRole($role, 'changeEmail'));
		$this->assertFalse($forOther->settingForRole($role, 'changeEmail'));
	}

	public function testUpdate(): void
	{
		$this->assertNull($this->permissions()->ensure('update'));
	}

	public function testUpdateWithoutPermission(): void
	{
		$permissions = $this->permissions(
			permissions: ['update' => false]
		);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.user.update.permission');

		$permissions->ensure('update');
	}
}
