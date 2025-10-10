<?php

namespace Kirby\Permissions;

use Kirby\Cms\TestCase as BaseTestCase;

class PermissionsTestCase extends BaseTestCase
{
	protected function assertPermissionsKeys(string $class, array $keys): void
	{
		$this->assertSame($keys, $class::keys());
	}
}
