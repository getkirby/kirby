<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagePermissions::class)]
class PagePermissionsTest extends PermissionsGroupTestCase
{
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
			'read',
			'sort',
			'update'
		]);
	}
}
