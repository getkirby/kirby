<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

class TestPermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $list = null,
		public bool|null $read = null,
		public bool|null $update = null,
	) {
	}

}

#[CoversClass(Permissions::class)]
class PermissionsTest extends PermissionsTestCase
{
	public function testConstruct(): void
	{
		$permissions = new TestPermissions();
		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testForAdmin(): void
	{
		$permissions = TestPermissions::forAdmin();
		$this->assertAllPermissionsAre($permissions, true);
	}

	public function testForNobody(): void
	{
		$permissions = TestPermissions::forNobody();
		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testFromArray(): void
	{
		$permissions = TestPermissions::from([
			'*'      => false,
			'delete' => true
		]);

		$this->assertFalse($permissions->access);
		$this->assertFalse($permissions->list);
		$this->assertTrue($permissions->delete);
	}

	public function testFromArrayWithWildcardKey(): void
	{
		$permissions = TestPermissions::from([
			'*' => true
		]);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = TestPermissions::from([
			'*' => false
		]);

		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testFromWildcard(): void
	{
		$permissions = TestPermissions::from(true);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = TestPermissions::from(false);

		$this->assertAllPermissionsAre($permissions, false);

		$permissions = TestPermissions::from(null);

		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(TestPermissions::class, [
			'access',
			'create',
			'delete',
			'list',
			'read',
			'update',
		]);
	}

	public function testMerge(): void
	{
		$defaultPermissions = new TestPermissions(
			list: true
		);

		$customPermissions = new TestPermissions(
			access: true,
			delete: false
		);

		$mergedPermissions = $defaultPermissions->merge($customPermissions);

		$this->assertTrue($mergedPermissions->access);
		$this->assertNull($mergedPermissions->create);
		$this->assertFalse($mergedPermissions->delete);
		$this->assertTrue($mergedPermissions->list);
		$this->assertNull($mergedPermissions->read);
		$this->assertNull($mergedPermissions->update);
	}

	public function testToArray(): void
	{
		$permissions = new TestPermissions();

		$this->assertSame([
			'access' => null,
			'create' => null,
			'delete' => null,
			'list'   => null,
			'read'   => null,
			'update' => null
		], $permissions->toArray());
	}

	public function testWildcard(): void
	{
		$permissions = new TestPermissions();

		$this->assertAllPermissionsAre($permissions, null);

		$permissions->wildcard(true);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions->wildcard(false);

		$this->assertAllPermissionsAre($permissions, false);

		$permissions->wildcard(null);

		$this->assertAllPermissionsAre($permissions, null);
	}

}
