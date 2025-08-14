<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\User;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserCreateDialogController::class)]
class UserCreateDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserCreateDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name' => 'admin',
					'title' => 'Admin',
				],
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
				]
			],
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('12345678')
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testLoad(): void
	{
		$controller = new UserCreateDialogController();
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();

		// check for all fields
		$this->assertSame('Name', $props['fields']['name']['label']);
		$this->assertSame('Email', $props['fields']['email']['label']);
		$this->assertSame('Password', $props['fields']['password']['label']);
		$this->assertSame('Language', $props['fields']['translation']['label']);
		$this->assertSame('Role', $props['fields']['role']['label']);

		$this->assertSame('Create', $props['submitButton']);

		// check values
		$this->assertSame('', $props['value']['name']);
		$this->assertSame('', $props['value']['email']);
		$this->assertSame('', $props['value']['password']);
		$this->assertSame('en', $props['value']['translation']);
		$this->assertSame('admin', $props['value']['role']);
	}

	public function testLoadWithRoleQuery(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'role' => 'editor',
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new UserCreateDialogController();
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('editor', $props['value']['role']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'name'  => 'Peter',
					'email' => 'test2@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$this->assertNull($this->app->user('test2@getkirby.com'));

		$controller = new UserCreateDialogController();
		$response   = $controller->submit();

		$this->assertSame('user.create', $response['event']);

		$user = $this->app->user('test2@getkirby.com');
		$this->assertSame('Peter', $user->name()->value());
		$this->assertSame('admin', $user->role()->name());
	}
}
