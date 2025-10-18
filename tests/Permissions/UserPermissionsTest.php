<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserPermissions::class)]
class UserPermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new UserPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(UserPermissions::class, [
			'access',
			'changeEmail',
			'changeLanguage',
			'changePassword',
			'changeRole',
			'create',
			'delete',
			'list',
			'read',
			'update',
		]);
	}
}
