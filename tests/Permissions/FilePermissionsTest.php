<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePermissions::class)]
class FilePermissionsTest extends PermissionsGroupTestCase
{
	public function testKeys(): void
	{
		$this->assertPermissionsKeys(FilePermissions::class, [
			'access',
			'changeName',
			'changeTemplate',
			'create',
			'delete',
			'list',
			'read',
			'replace',
			'sort',
			'update',
		]);
	}
}
