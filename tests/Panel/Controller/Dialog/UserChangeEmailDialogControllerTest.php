<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDialogController::class)]
#[CoversClass(UserChangeEmailDialogController::class)]
class UserChangeEmailDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserChangeEmailDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = UserChangeEmailDialogController::factory('test');
		$this->assertInstanceOf(UserChangeEmailDialogController::class, $controller);
		$this->assertSame('test', $controller->user->id());
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserChangeEmailDialogController($user);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Email', $props['fields']['email']['label']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame('test@getkirby.com', $props['value']['email']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'email' => 'test2@getkirby.com'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$user = $this->app->user('test');
		$this->assertSame('test@getkirby.com', $user->email());

		$controller = new UserChangeEmailDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.changeEmail', $response['event']);

		// reload the user freshly
		$user = $this->app->user('test');
		$this->assertSame('test2@getkirby.com', $user->email());
	}
}
