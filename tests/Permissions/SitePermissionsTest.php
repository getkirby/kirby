<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SitePermissions::class)]
class SitePermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new SitePermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(SitePermissions::class, [
			'access',
			'changeTitle',
			'read',
			'update',
		]);
	}
}
