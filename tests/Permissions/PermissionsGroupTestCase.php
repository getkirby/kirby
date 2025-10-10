<?php

namespace Kirby\Permissions;

class PermissionsGroupTestCase extends PermissionsTestCase
{
	protected function assertAllPermissionsAre(ModelPermissions $permissions, bool|null $expected): void
	{
		foreach ($permissions::keys() as $key) {
			$this->assertSame($expected, $permissions->$key);
		}
	}
}
