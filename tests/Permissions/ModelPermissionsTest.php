<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelPermissions::class)]
class ModelPermissionsTest extends PermissionsGroupTestCase
{
	public function testForAdmin(): void
	{
		$permissions = ModelPermissions::forAdmin();
		$this->assertAllPermissionsAre($permissions, true);
	}

	public function testForNobody(): void
	{
		$permissions = ModelPermissions::forNobody();
		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testFromArrayWithRoleMatrix(): void
	{
		$permissions = ModelPermissions::fromArray([
			'*' => false,
			'access' => [
				'*'     => false,
				'admin' => true
			],
			'delete' => true
		]);

		$this->assertFalse($permissions->access);
		$this->assertTrue($permissions->delete);
		$this->assertFalse($permissions->list);

		$permissions = ModelPermissions::fromArray([
			'*' => false,
			'access' => [
				'*'     => false,
				'admin' => true
			],
			'delete' => true
		], 'admin');

		$this->assertTrue($permissions->access);
		$this->assertTrue($permissions->delete);
		$this->assertFalse($permissions->list);
	}

	public function testFromArrayWithWildcardKey(): void
	{
		$permissions = ModelPermissions::fromArray([
			'*' => true
		]);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = ModelPermissions::fromArray([
			'*' => false
		]);

		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testFromWildcard(): void
	{
		$permissions = ModelPermissions::fromWildcard(true);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = ModelPermissions::fromWildcard(false);

		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(ModelPermissions::class, [
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
		$defaultPermissions = new ModelPermissions(
			list: true
		);

		$customPermissions = new ModelPermissions(
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
		$permissions = new ModelPermissions();

		$this->assertSame([
			'access' => null,
			'create' => null,
			'delete' => null,
			'list'   => null,
			'read'   => null,
			'update' => null
		], $permissions->toArray());
	}
}
