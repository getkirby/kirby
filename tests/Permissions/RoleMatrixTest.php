<?php

namespace Kirby\Permissions;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RoleMatrix::class)]
class RoleMatrixTest extends TestCase
{
	public function testWithBool(): void
	{
		/**
		 * access: true
		 */
		$this->assertTrue(RoleMatrix::toPermission(true));

		/**
		 * access: false
		 */
		$this->assertFalse(RoleMatrix::toPermission(false));
	}

	public function testWithWildcard(): void
	{
		/**
		 * access:
		 *   '*': true
		 */
		$this->assertTrue(RoleMatrix::toPermission([
			'*' => true
		]));

		/**
		 * access:
		 *   '*': false
		 */
		$this->assertFalse(RoleMatrix::toPermission([
			'*' => false
		]));

		/**
		 * access:
		 *   '*': false
		 *   admin: true
		 */
		$this->assertFalse(RoleMatrix::toPermission([
			'*'     => false,
			'admin' => true
		]));

		/**
		 * access:
		 *   '*': false
		 *   admin: true
		 */
		$this->assertTrue(RoleMatrix::toPermission([
			'*'     => false,
			'admin' => true
		], 'admin'));
	}

	public function testWithUndefinedPermissions(): void
	{
		$this->assertTrue(RoleMatrix::toPermission([]));
	}
}
