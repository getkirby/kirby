<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AccountPermissions::class)]
class AccountPermissionsTest extends PermissionsGroupTestCase
{
	public function testKeys(): void
	{
		$this->assertPermissionsKeys(AccountPermissions::class, [
			'access',
			'changeEmail',
			'changeLanguage',
			'changePassword',
			'changeRole',
			'delete',
			'list',
			'read',
			'update',
		]);
	}
}
