<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDialogController::class)]
#[CoversClass(UserChangeRoleDialogController::class)]
class UserChangeRoleDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserChangeRoleDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
				],
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				],
				[
					'id'    => 'admin',
					'email' => 'admin@getkirby.com',
					'role'  => 'admin',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = UserChangeRoleDialogController::factory('test');
		$this->assertInstanceOf(UserChangeRoleDialogController::class, $controller);
		$this->assertSame('test', $controller->user->id());
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserChangeRoleDialogController($user);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Select a new role', $props['fields']['role']['label']);
		$this->assertSame('Change role', $props['submitButton']);
		$this->assertSame('admin', $props['value']['role']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'role' => 'editor'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$user = $this->app->user('test');
		$this->assertSame('admin', $user->role()->name());

		$controller = new UserChangeRoleDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.changeRole', $response['event']);

		// reload the user freshly
		$user = $this->app->user($user->id());
		$this->assertSame('editor', $user->role()->name());
	}
}
