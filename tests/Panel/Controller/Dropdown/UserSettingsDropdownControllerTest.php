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
}
