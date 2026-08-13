<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

class ExtendedModelPermissions extends ModelPermissions
{
	public function category(): string
	{
		return 'pages';
	}
}

#[CoversClass(ModelPermissions::class)]
class ModelPermissionsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.ModelPermissions';

	protected function pageWithOptions(array $options = []): Page
	{
		return new Page([
			'slug'      => 'test',
			'blueprint' => [
				'name'    => 'default',
				'options' => $options
			]
		]);
	}

	protected function permissions(
		array|bool $permissions = [],
		array $options = []
	): ExtendedModelPermissions {
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

		return new ExtendedModelPermissions(
			model: $this->pageWithOptions($options),
			user: $this->app->user()
		);
	}

	public function testEnsureWithDedicatedMethod(): void
	{
		$this->app->impersonate('kirby');

		$permissions = new PagePermissions(
			model: $this->pageWithOptions(),
			user: $this->app->user()
		);

		$this->assertNull($permissions->ensure('changeTitle'));
	}

	public function testEnsureWithSharedMethod(): void
	{
		// shared methods of the base class must never be
		// mistaken for a dedicated action method
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.setting');

		$this->permissions()->ensure('setting', default: false);
	}

	public function testEnsureWithUndefinedAction(): void
	{
		$permissions = $this->permissions();

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.does-not-exist');

		// actions without any rule are denied by default
		$permissions->ensure('does-not-exist');
	}

	public function testEnsureWithUndefinedActionAndDefault(): void
	{
		$this->assertNull($this->permissions()->ensure('does-not-exist', default: true));
	}

	public function testMay(): void
	{
		$this->assertTrue($this->permissions()->may('update'));
	}

	public function testMayWithFailingPermission(): void
	{
		$this->assertFalse($this->permissions(['update' => false])->may('update'));
	}

	public function testMayWithUndefinedAction(): void
	{
		$permissions = $this->permissions();

		$this->assertFalse($permissions->may('does-not-exist'));
		$this->assertTrue($permissions->may('does-not-exist', default: true));
	}

	public function testError(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.test');

		$this->permissions()->error('error.test');
	}

	public function testSetting(): void
	{
		$this->assertTrue($this->permissions()->setting('update'));
	}

	public function testSettingForRole(): void
	{
		$permissions = $this->permissions(['update' => false]);
		$role        = $this->app->role('editor');

		$this->assertFalse($permissions->settingForRole($role, 'update'));
		$this->assertNull($permissions->settingForRole($role, 'does-not-exist'));
	}

	public function testSettingForUser(): void
	{
		$permissions = $this->permissions(
			options: ['update' => ['editor' => false]]
		);

		$this->assertFalse(
			$permissions->settingForUser($this->app->user(), 'update')
		);
		$this->assertNull(
			$permissions->settingForUser($this->app->user(), 'delete')
		);
	}

	public function testSettingWithBlueprintOptionTakingPrecedence(): void
	{
		$permissions = $this->permissions(
			permissions: ['update' => false],
			options: ['update' => ['editor' => true]]
		);

		$this->assertTrue($permissions->setting('update'));
	}

	public function testSettingWithDisabledPermission(): void
	{
		$permissions = $this->permissions(['update' => false]);

		$this->assertFalse($permissions->setting('update'));
	}

	public function testSettingWithKirbyUser(): void
	{
		// the almighty `kirby` user is not affected by any rule
		$this->permissions(
			permissions: ['update' => false],
			options: ['update' => false]
		);

		$this->app->impersonate('kirby');

		$permissions = new ExtendedModelPermissions(
			model: $this->pageWithOptions(['update' => false]),
			user: $this->app->user()
		);

		$this->assertTrue($permissions->setting('update'));
	}

	public function testSettingWithNobodyRole(): void
	{
		// the `nobody` role must be denied for real user accounts
		// as well, not just for the virtual, logged out user
		$this->app = $this->app->clone([
			'roles' => [
				// a role blueprint without a `permissions` key
				// grants every permission
				['name' => 'nobody', 'title' => 'Nobody']
			],
			'users' => [
				['id' => 'test', 'role' => 'nobody']
			]
		]);

		$user = $this->app->user('test');

		$this->assertFalse($user->isNobody());
		$this->assertTrue($user->role()->isNobody());

		$permissions = new ExtendedModelPermissions(
			model: $this->pageWithOptions(['update' => true]),
			user: $user
		);

		$this->assertFalse($permissions->setting('update'));
	}

	public function testSettingWithNobodyUser(): void
	{
		// a blueprint option must never grant a permission
		// to a user with the `nobody` role
		$permissions = new ExtendedModelPermissions(
			model: $this->pageWithOptions(['update' => true]),
			user: User::nobody()
		);

		$this->assertFalse($permissions->setting('update'));
	}

	public function testSettingWithUndefinedAction(): void
	{
		$this->assertNull($this->permissions()->setting('does-not-exist'));
	}

	public function testSettingWithUndefinedActionAndDefault(): void
	{
		$permissions = $this->permissions();

		$this->assertTrue($permissions->setting('does-not-exist', default: true));
		$this->assertFalse($permissions->setting('does-not-exist', default: false));
	}

	public function testSettingWithUndefinedActionAndKirbyUser(): void
	{
		// the almighty `kirby` user overrules the default
		$this->app->impersonate('kirby');

		$permissions = new ExtendedModelPermissions(
			model: $this->pageWithOptions(),
			user: $this->app->user()
		);

		$this->assertTrue($permissions->setting('does-not-exist', default: false));
	}

	public function testSettingWithUndefinedActionAndNobodyUser(): void
	{
		// the `nobody` role overrules the default
		$permissions = new ExtendedModelPermissions(
			model: $this->pageWithOptions(),
			user: User::nobody()
		);

		$this->assertFalse($permissions->setting('does-not-exist', default: true));
	}
}
