<?php

namespace Kirby\Permissions;

use Kirby\Cms\TestCase as BaseTestCase;

class PermissionsTestCase extends BaseTestCase
{
	protected function assertAllPermissionsAre(Permissions $permissions, bool|null $expected): void
	{
		foreach ($permissions::keys() as $key) {
			$this->assertSame($expected, $permissions->$key);
		}
	}

	protected function assertPermissionsKeys(string $class, array $keys): void
	{
		$this->assertSame($keys, $class::keys());
	}
}
