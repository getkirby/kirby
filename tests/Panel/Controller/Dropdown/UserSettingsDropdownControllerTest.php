<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\User;
use Kirby\Panel\TestCase;
use Kirby\Toolkit\A;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserSettingsDropdownController::class)]
#[CoversClass(ModelSettingsDropdownController::class)]
class UserSettingsDropdownControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dropdown.UserSettingsDropdownController';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'password' => User::hashPassword('12345678')
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com'
				],
				[
					'id'    => 'admin',
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$user       = $this->app->user('test');
		$controller = UserSettingsDropdownController::factory('test');
		$this->assertInstanceOf(UserSettingsDropdownController::class, $controller);
		$this->assertSame($user, $controller->model());
	}

	public function testIsDisabledOption(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$this->assertFalse($controller->isDisabledOption('changeName'));
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$options    = $controller->load();
		$this->assertCount(9, $options);
	}

	public function testLoadChangePasswordForOtherUsers(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'password' => User::hashPassword('12345678')
				],
				[
					'id'    => 'nopassword',
					'email' => 'nopassword@getkirby.com',
					'role'  => 'editor'
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				],
				[
					'id'    => 'admin',
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$option = fn (string $id) => A::find(
			(new UserSettingsDropdownController($this->app->user($id)))->load(),
			fn ($option) => ($option['dialog'] ?? null) === '/users/' . $id . '/changePassword'
		);

		$this->assertSame('Change password', $option('test')['text']);
		$this->assertFalse($option('test')['disabled']);

		$this->assertSame('Set password', $option('nopassword')['text']);

		$this->assertTrue($option('admin')['disabled']);
	}

	public function testLoadChangePasswordWithoutPasswordMethod(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['code']
				]
			],
			'roles' => [
				['name' => 'admin'],
				['name' => 'editor']
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com'
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$controller = new UserSettingsDropdownController($this->app->user('test'));
		$dialogs    = array_column($controller->load(), 'dialog');

		$this->assertNotContains('/users/test/changePassword', $dialogs);
	}
}
