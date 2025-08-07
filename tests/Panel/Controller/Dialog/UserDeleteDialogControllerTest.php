<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\User;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDialogController::class)]
#[CoversClass(UserDeleteDialogController::class)]
class UserDeleteDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserDeleteDialogController';

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
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'password' => User::hashPassword('12345678'),
					'role'     => 'admin',
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
		$controller = UserDeleteDialogController::factory('test');
		$this->assertInstanceOf(UserDeleteDialogController::class, $controller);
		$this->assertSame('test', $controller->user->id());
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserDeleteDialogController($user);
		$dialog     = $controller->load();
		$this->assertInstanceOf(RemoveDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Do you really want to delete <br><strong>test@getkirby.com</strong>?', $props['text']);
	}

	public function testSubmit(): void
	{
		$this->assertCount(2, $this->app->users());

		$user       = $this->app->user('test');
		$controller = new UserDeleteDialogController($user);
		$response   = $controller->submit();

		$this->assertCount(1, $this->app->users());
		$this->assertSame('user.delete', $response['event']);
		$this->assertNull($response['redirect']);
	}

	public function testSubmitWithReferrer(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => '/users/test'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$user = $this->app->user('test');
		$controller = new UserDeleteDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.delete', $response['event']);
		$this->assertSame('/users', $response['redirect']);
	}

	public function testSubmitWithOwnAccount(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_referrer' => '/users/test'
				]
			]
		]);

		$this->app->impersonate('test');

		$this->app->impersonate('test@getkirby.com');

		$user = $this->app->user('test');
		$controller = new UserDeleteDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.delete', $response['event']);
		$this->assertSame('/logout', $response['redirect']);
	}
}
