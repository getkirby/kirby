<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\ModelTestCase;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguagePermissions::class)]
class LanguagePermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.LanguagePermissions';

	protected function permissions(array|bool $permissions = []): LanguagePermissions
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'languages' => $permissions
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		return new LanguagePermissions(
			model: new Language(['code' => 'de', 'name' => 'Deutsch']),
			user: $this->app->user()
		);
	}

	public function testCategory(): void
	{
		$this->assertSame('languages', $this->permissions()->category());
	}

	public function testCreate(): void
	{
		$this->assertNull($this->permissions()->ensure('create'));
	}

	public function testCreateWithoutPermission(): void
	{
		$permissions = $this->permissions(['create' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.language.create.permission');

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
		$this->expectExceptionCode('error.language.delete.permission');

		$permissions->ensure('delete');
	}

	public function testError(): void
	{
		$permissions = $this->permissions();

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.language.test.permission');

		$permissions->error('test');
	}

	public function testSettingForUser(): void
	{
		$permissions = $this->permissions();

		// languages have no model blueprint, so there is
		// never a user-specific rule for them
		$this->assertNull(
			$permissions->settingForUser($this->app->user(), 'update')
		);
	}

	public function testUpdate(): void
	{
		$this->assertNull($this->permissions()->ensure('update'));
	}

	public function testUpdateWithoutPermission(): void
	{
		$permissions = $this->permissions(['update' => false]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.language.update.permission');

		$permissions->ensure('update');
	}
}
