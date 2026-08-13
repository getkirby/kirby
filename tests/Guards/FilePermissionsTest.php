<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePermissions::class)]
class FilePermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.FilePermissions';

	protected function permissions(array|bool $permissions = []): FilePermissions
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'files' => $permissions
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => new Page(['slug' => 'test'])
		]);

		return new FilePermissions(
			model: $file,
			user: $this->app->user()
		);
	}

	public function testCategory(): void
	{
		$this->assertSame('files', $this->permissions()->category());
	}

	public function testChangeName(): void
	{
		$this->assertNull($this->permissions()->ensure('changeName'));
	}

	public function testChangeNameWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeName' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.changeName.permission');

		$permissions->ensure('changeName');
	}

	public function testChangeTemplate(): void
	{
		$this->assertNull($this->permissions()->ensure('changeTemplate'));
	}

	public function testChangeTemplateWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeTemplate' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.changeTemplate.permission');

		$permissions->ensure('changeTemplate');
	}

	public function testCreate(): void
	{
		$this->assertNull($this->permissions()->ensure('create'));
	}

	public function testCreateWithoutPermission(): void
	{
		$permissions = $this->permissions(['create' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.create.permission');

		$permissions->ensure('create');
	}

	public function testDelete(): void
	{
		$this->assertNull($this->permissions()->ensure('delete'));
	}

	public function testDeleteWithoutPermission(): void
	{
		$permissions = $this->permissions(['delete' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.delete.permission');

		$permissions->ensure('delete');
	}

	public function testError(): void
	{
		$permissions = $this->permissions();

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.test.permission');

		$permissions->error('test');
	}

	public function testReplace(): void
	{
		$this->assertNull($this->permissions()->ensure('replace'));
	}

	public function testReplaceWithoutPermission(): void
	{
		$permissions = $this->permissions(['replace' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.replace.permission');

		$permissions->ensure('replace');
	}

	public function testSort(): void
	{
		$this->assertNull($this->permissions()->ensure('sort'));
	}

	public function testSortWithoutPermission(): void
	{
		$permissions = $this->permissions(['sort' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.sort.permission');

		$permissions->ensure('sort');
	}

	public function testUpdate(): void
	{
		$this->assertNull($this->permissions()->ensure('update'));
	}

	public function testUpdateWithoutPermission(): void
	{
		$permissions = $this->permissions(['update' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.file.update.permission');

		$permissions->ensure('update');
	}
}
