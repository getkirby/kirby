<?php

namespace Kirby\Permissions;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Permissions::class)]
class PermissionsTest extends PermissionsTestCase
{
	protected function assertAllPermissionsAre(Permissions $permissions, bool $expected): void
	{
		foreach ($permissions::keys() as $key) {
			$subset = $permissions->$key;

			foreach ($subset::keys() as $capability) {
				$this->assertSame($expected, $subset->$capability);
			}
		}
	}

	public function testFromArrayWithWildcardKey(): void
	{
		$permissions = Permissions::fromArray([
			'*' => true
		]);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = Permissions::fromArray([
			'*' => false
		]);

		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testFromWildcard(): void
	{
		$permissions = Permissions::fromWildcard(true);

		$this->assertAllPermissionsAre($permissions, true);

		$permissions = Permissions::fromWildcard(false);

		$this->assertAllPermissionsAre($permissions, false);
	}

	public function testKeys(): void
	{
		$this->assertPermissionsKeys(Permissions::class, [
			'account',
			'file',
			'files',
			'language',
			'languages',
			'languageVariable',
			'languageVariables',
			'page',
			'pages',
			'panel',
			'site',
			'system',
			'user',
			'users'
		]);
	}

	public function testToArray(): void
	{
		$permissions = new Permissions();
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
			'files' => [
				'access' => null,
				'create' => null,
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
				'create' => null,
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
				'create' => null,
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
				'read'           => null,
				'sort'           => null,
				'update'         => null,
			],
			'pages' => [
				'access' => null,
				'create' => null,
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
				'create' => null,
			]
		];

		$this->assertSame($expected, $result);
	}

}
