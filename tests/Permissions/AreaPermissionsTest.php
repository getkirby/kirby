<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AreaPermissions::class)]
class AreaPermissionsTest extends PermissionsGroupTestCase
{
	public function testKeys(): void
	{
		$this->assertPermissionsKeys(AreaPermissions::class, [
			'access',
		]);
	}
}
