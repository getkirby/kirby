<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsersPermissions::class)]
class UsersPermissionsTest extends PermissionsGroupTestCase
{
	public function testKeys(): void
	{
		$this->assertPermissionsKeys(UsersPermissions::class, [
			'access',
			'create'
		]);
	}
}
