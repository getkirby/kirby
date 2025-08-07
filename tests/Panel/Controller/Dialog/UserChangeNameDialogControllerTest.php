<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDialogController::class)]
#[CoversClass(UserChangeNameDialogController::class)]
class UserChangeNameDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserChangeNameDialogController';

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
		$controller = UserChangeNameDialogController::factory('test');
		$this->assertInstanceOf(UserChangeNameDialogController::class, $controller);
		$this->assertSame('test', $controller->user->id());
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserChangeNameDialogController($user);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Name', $props['fields']['name']['label']);
		$this->assertSame('Rename', $props['submitButton']);
		$this->assertNull($props['value']['name']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'name' => 'Peter'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$user = $this->app->user('test');
		$this->assertNull($user->name()->value());

		$controller = new UserChangeNameDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.changeName', $response['event']);

		// reload the user freshly
		$user = $this->app->user($user->id());
		$this->assertSame('Peter', $user->name()->value());
	}
}
