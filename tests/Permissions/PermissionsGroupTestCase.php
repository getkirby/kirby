<?php

namespace Kirby\Permissions;

use Kirby\Permissions\Abstracts\PermissionsGroup;

class PermissionsGroupTestCase extends PermissionsTestCase
{
	protected function assertAllPermissionsAre(PermissionsGroup $permissions, bool|null $expected): void
	{
		foreach ($permissions::keys() as $key) {
			$this->assertSame($expected, $permissions->$key);
		}
	}
}
