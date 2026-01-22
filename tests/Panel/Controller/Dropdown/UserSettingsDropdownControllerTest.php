<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\User;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserSettingsDropdownController::class)]
#[CoversClass(ModelSettingsDropdownController::class)]
class UserSettingsDropdownControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dropdown.UserSettingsDropdownController';

	public function setUp(): void
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

	public function testLoadWithTotpEnable(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true]
					]
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$options    = $controller->load();
		$this->assertCount(10, $options);
		$this->assertSame('/account/totp/enable', $options[7]['dialog']);
	}

	public function testLoadWithTotpDisable(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true]
					]
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$user = $this->app->user('test')->changeSecret('totp', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$controller = new UserSettingsDropdownController($user);
		$options    = $controller->load();
		$this->assertCount(10, $options);
		$this->assertSame('/account/totp/disable', $options[7]['dialog']);
	}

	public function testTotpMode(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$this->assertNull($controller->totpMode());

		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => [
						'password' => ['2fa' => true]
					]
				]
			]
		]);

		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$this->assertNull($controller->totpMode());

		$this->app->impersonate('test@getkirby.com');

		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$this->assertSame('enable', $controller->totpMode());

		$user = $user->changeSecret('totp', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
		$controller = new UserSettingsDropdownController($user);
		$this->assertSame('disable', $controller->totpMode());

		$this->app->impersonate('admin@getkirby.com');
		$controller = new UserSettingsDropdownController($user);
		$this->assertSame('disable', $controller->totpMode());

		$user = $user->changeSecret('totp', null);
		$controller = new UserSettingsDropdownController($user);
		$this->assertNull($controller->totpMode());
	}

	public function testLoadOptions(): void
	{
		$user       = $this->app->user('editor');
		$controller = new UserSettingsDropdownController($user);
		$options    = $controller->load();

		$name = $options[0];
		$this->assertSame('/users/editor/changeName', $name['dialog']);
		$this->assertSame('Rename this user', $name['text']);

		$this->assertSame('-', $options[1]);

		$email = $options[2];
		$this->assertSame('/users/editor/changeEmail', $email['dialog']);
		$this->assertSame('Change email', $email['text']);

		$role = $options[3];
		$this->assertSame('/users/editor/changeRole', $role['dialog']);
		$this->assertSame('Change role', $role['text']);

		$language = $options[4];
		$this->assertSame('/users/editor/changeLanguage', $language['dialog']);
		$this->assertSame('Change language', $language['text']);

		$this->assertSame('-', $options[5]);

		$password = $options[6];
		$this->assertSame('/users/editor/changePassword', $password['dialog']);
		$this->assertSame('Set password', $password['text']);

		$this->assertSame('-', $options[7]);

		$delete = $options[8];
		$this->assertSame('/users/editor/delete', $delete['dialog']);
		$this->assertSame('Delete this user', $delete['text']);
	}

	public function testLoadOptionsWithPassword(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserSettingsDropdownController($user);
		$options    = $controller->load();

		$password = $options[6];
		$this->assertSame('Change password', $password['text']);
	}
}
