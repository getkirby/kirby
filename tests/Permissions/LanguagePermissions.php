<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguagePermissions::class)]
class LanguagePermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new LanguagePermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(LanguagePermissions::class, [
			'access',
			'create',
			'delete',
			'list',
			'read',
			'update',
		]);
	}
}
