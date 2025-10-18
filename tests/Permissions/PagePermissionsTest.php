<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagePermissions::class)]
class PagePermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new PagePermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(PagePermissions::class, [
			'access',
			'changeSlug',
			'changeStatus',
			'changeTemplate',
			'changeTitle',
			'create',
			'delete',
			'duplicate',
			'list',
			'move',
			'preview',
			'read',
			'sort',
			'update'
		]);
	}
}
