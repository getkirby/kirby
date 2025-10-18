<?php

namespace Kirby\Permissions;

use Kirby\Cms\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Loader::class)]
class LoaderTest extends TestCase
{
	public function testRoleMatrixToPermissionWithBool(): void
	{
		/**
		 * access: true
		 */
		$this->assertTrue(Loader::roleMatrixToPermission(true));

		/**
		 * access: false
		 */
		$this->assertFalse(Loader::roleMatrixToPermission(false));
	}

	public function testRoleMatrixToPermissionWithWildcard(): void
	{
		/**
		 * access:
		 *   '*': true
		 */
		$this->assertTrue(Loader::roleMatrixToPermission([
			'*' => true
		]));

		/**
		 * access:
		 *   '*': false
		 */
		$this->assertFalse(Loader::roleMatrixToPermission([
			'*' => false
		]));

		/**
		 * access:
		 *   '*': false
		 *   admin: true
		 */
		$this->assertFalse(Loader::roleMatrixToPermission([
			'*'     => false,
			'admin' => true
		]));

		/**
		 * access:
		 *   '*': false
		 *   admin: true
		 */
		$this->assertTrue(Loader::roleMatrixToPermission([
			'*'     => false,
			'admin' => true
		], 'admin'));
	}

	public function testRoleMatrixToPermissionWithUndefinedPermissions(): void
	{
		$this->assertTrue(Loader::roleMatrixToPermission([]));
	}
}
