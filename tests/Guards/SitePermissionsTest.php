<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SitePermissions::class)]
class SitePermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.SitePermissions';

	protected function permissions(array|bool $permissions = []): SitePermissions
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => $permissions
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		return new SitePermissions(
			model: $this->app->site(),
			user: $this->app->user()
		);
	}

	public function testCategory(): void
	{
		$this->assertSame('site', $this->permissions()->category());
	}

	public function testChangeTitle(): void
	{
		$this->assertNull($this->permissions()->ensure('changeTitle'));
	}

	public function testChangeTitleWithoutPermission(): void
	{
		$permissions = $this->permissions(['changeTitle' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.site.changeTitle.permission');

		$permissions->ensure('changeTitle');
	}

	public function testError(): void
	{
		$permissions = $this->permissions();

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.site.test.permission');

		$permissions->error('test');
	}

	public function testUpdate(): void
	{
		$this->assertNull($this->permissions()->ensure('update'));
	}

	public function testUpdateWithoutPermission(): void
	{
		$permissions = $this->permissions(['update' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.site.update.permission');

		$permissions->ensure('update');
	}
}
