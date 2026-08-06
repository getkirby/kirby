<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagePermissions::class)]
class PagePermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.PagePermissions';

	protected function permissions(array|bool $permissions = []): PagePermissions
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'pages' => $permissions
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		return new PagePermissions(
			model: new Page(['slug' => 'test']),
			user: $this->app->user()
		);
	}

	public function testCategory(): void
	{
		$this->assertSame('pages', $this->permissions()->category());
	}

	public function testChangeSlug(): void
	{
		$this->assertNull($this->permissions()->ensure('changeSlug'));
	}

	public function testChangeSlugWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeSlug' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeSlug.permission');

		$permissions->ensure('changeSlug');
	}

	public function testChangeStatus(): void
	{
		$this->assertNull($this->permissions()->ensure('changeStatus'));
	}

	public function testChangeStatusWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeStatus' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeStatus.permission');

		$permissions->ensure('changeStatus');
	}

	public function testChangeStatusToDraft(): void
	{
		$this->assertNull($this->permissions()->ensure('changeStatusToDraft'));
	}

	public function testChangeStatusToDraftWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeStatus' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeStatus.permission');

		$permissions->ensure('changeStatusToDraft');
	}

	public function testChangeTemplate(): void
	{
		$this->assertNull($this->permissions()->ensure('changeTemplate'));
	}

	public function testChangeTemplateWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeTemplate' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeTemplate.permission');

		$permissions->ensure('changeTemplate');
	}

	public function testChangeTitle(): void
	{
		$this->assertNull($this->permissions()->ensure('changeTitle'));
	}

	public function testChangeTitleWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeTitle' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeTitle.permission');

		$permissions->ensure('changeTitle');
	}

	public function testCreate(): void
	{
		$this->assertNull($this->permissions()->ensure('create'));
	}

	public function testCreateWithoutPermission(): void
	{
		$permissions = $this->permissions(['create' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.create.permission');

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
		$this->expectExceptionCode('error.page.delete.permission');

		$permissions->ensure('delete');
	}

	public function testDuplicate(): void
	{
		$this->assertNull($this->permissions()->ensure('duplicate'));
	}

	public function testDuplicateWithoutPermission(): void
	{
		$permissions = $this->permissions(['duplicate' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.duplicate.permission');

		$permissions->ensure('duplicate');
	}

	public function testError(): void
	{
		$permissions = $this->permissions();

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.test.permission');

		$permissions->error('test');
	}

	public function testMove(): void
	{
		$this->assertNull($this->permissions()->ensure('move'));
	}

	public function testMoveWithoutPermission(): void
	{
		$permissions = $this->permissions(['move' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.move.permission');

		$permissions->ensure('move');
	}

	public function testPublish(): void
	{
		$this->assertNull($this->permissions()->ensure('publish'));
	}

	public function testPublishWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeStatus' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeStatus.permission');

		$permissions->ensure('publish');
	}

	public function testSort(): void
	{
		$this->assertNull($this->permissions()->ensure('sort'));
	}

	public function testSortWithoutPermission(): void
	{
		$permissions = $this->permissions(['sort' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.sort.permission');

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
		$this->expectExceptionCode('error.page.update.permission');

		$permissions->ensure('update');
	}
}
