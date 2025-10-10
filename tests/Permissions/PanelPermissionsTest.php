<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PanelPermissions::class)]
class PanelPermissionsTest extends PermissionsGroupTestCase
{
	public function testConstruct(): void
	{
		$permissions = new PanelPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(PanelPermissions::class, [
			'access',
		]);
	}
}
