<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SystemPermissions::class)]
class SystemPermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new SystemPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(SystemPermissions::class, [
			'access',
		]);
	}
}
