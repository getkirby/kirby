<?php

namespace Kirby\Panel\Controller\Dropdown;

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
					'id'    => 'test',
					'email' => 'test@getkirby.com'
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
}
