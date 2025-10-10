<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageVariablePermissions::class)]
class LanguageVariablePermissionsTest extends PermissionsGroupTestCase
{
	public function testKeys(): void
	{
		$this->assertPermissionsKeys(LanguageVariablePermissions::class, [
			'access',
			'create',
			'delete',
			'list',
			'read',
			'update',
		]);
	}
}
