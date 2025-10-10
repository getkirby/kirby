<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SitePermissions::class)]
class SitePermissionsTest extends PermissionsGroupTestCase
{
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
