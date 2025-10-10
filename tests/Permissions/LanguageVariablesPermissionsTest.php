<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageVariablesPermissions::class)]
class LanguageVariablesPermissionsTest extends PermissionsGroupTestCase
{
	public function testConstruct(): void
	{
		$permissions = new LanguageVariablesPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(LanguageVariablesPermissions::class, [
			'access',
			'create'
		]);
	}
}
