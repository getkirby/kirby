<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsersPermissions::class)]
class UsersPermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new UsersPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(UsersPermissions::class, [
			'access',
		]);
	}
}
