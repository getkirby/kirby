<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(KirbyPermissions::class)]
class KirbyPermissionsTest extends PermissionsTestCase
{
	protected function assertAllPermissionsAre(Permissions $permissions, bool|null $expected): void
	{
		foreach ($permissions::keys() as $key) {
			$subset = $permissions->$key;

			foreach ($subset::keys() as $action) {
				$this->assertSame($expected, $subset->$action);
			}
		}
	}

	public function testFromArrayWithWildcardKey(): void
	{
		$permissions = KirbyPermissions::from([
			'*' => true
		]);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = KirbyPermissions::from([
			'*' => false
		]);

		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testFromWildcard(): void
	{
		$permissions = KirbyPermissions::from(true);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = KirbyPermissions::from(false);

		$this->assertAllPermissionsAre($permissions, false);

		$permissions = KirbyPermissions::from(null);

		$this->assertAllPermissionsAre($permissions, null);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(KirbyPermissions::class, [
			'account',
			'file',
			'language',
			'languages',
			'languageVariable',
			'languageVariables',
			'page',
			'panel',
			'site',
			'system',
			'user',
			'users'
		]);
	}

	public function testToArray(): void
	{
		$permissions = new KirbyPermissions();
		$result      = $permissions->toArray();

		$expected = [
			'account' => [
				'access'         => null,
				'changeEmail'    => null,
				'changeLanguage' => null,
				'changePassword' => null,
				'changeRole'     => null,
				'delete'         => null,
				'list'           => null,
				'read'           => null,
				'update'         => null,
			],
			'file' => [
				'access'         => null,
				'changeName'     => null,
				'changeTemplate' => null,
				'create'         => null,
				'delete'         => null,
				'list'           => null,
				'read'           => null,
				'replace'        => null,
				'sort'           => null,
				'update'         => null,
			],
			'language' => [
				'access' => null,
				'create' => null,
				'delete' => null,
				'list'   => null,
				'read'   => null,
				'update' => null,
			],
			'languages' => [
				'access' => null,
			],
			'languageVariable' => [
				'access' => null,
				'create' => null,
				'delete' => null,
				'list'   => null,
				'read'   => null,
				'update' => null,
			],
			'languageVariables' => [
				'access' => null,
			],
			'page' => [
				'access'         => null,
				'changeSlug'     => null,
				'changeStatus'   => null,
				'changeTemplate' => null,
				'changeTitle'    => null,
				'create'         => null,
				'delete'         => null,
				'duplicate'      => null,
				'list'           => null,
				'move'           => null,
				'preview'        => null,
				'read'           => null,
				'sort'           => null,
				'update'         => null,
			],
			'panel' => [
				'access' => null,
			],
			'site' => [
				'access'      => null,
				'changeTitle' => null,
				'read'        => null,
				'update'      => null,
			],
			'system' => [
				'access' => null,
			],
			'user' => [
				'access' => null,
				'changeEmail' => null,
				'changeLanguage' => null,
				'changePassword' => null,
				'changeRole' => null,
				'create' => null,
				'delete' => null,
				'list' => null,
				'read' => null,
				'update' => null,
			],
			'users' => [
				'access' => null,
			]
		];

		$this->assertSame($expected, $result);
	}

}
