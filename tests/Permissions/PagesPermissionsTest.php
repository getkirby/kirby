<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagesPermissions::class)]
class PagesPermissionsTest extends PermissionsGroupTestCase
{
	public function testConstruct(): void
	{
		$permissions = new PagesPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(PagesPermissions::class, [
			'access',
			'create'
		]);
	}
}
