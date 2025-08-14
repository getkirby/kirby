<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDialogController::class)]
#[CoversClass(UserChangeLanguageDialogController::class)]
class UserChangeLanguageDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserChangeLanguageDialogController';

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
		$controller = UserChangeLanguageDialogController::factory('test');
		$this->assertInstanceOf(UserChangeLanguageDialogController::class, $controller);
		$this->assertSame('test', $controller->user->id());
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserChangeLanguageDialogController($user);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Language', $props['fields']['translation']['label']);
		$this->assertSame('Change', $props['submitButton']);
		$this->assertSame('en', $props['value']['translation']);
	}

	public function testSubmit(): void
	{
		$user = $this->app->user('test');
		$this->assertSame('en', $user->language());

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'translation' => 'de'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new UserChangeLanguageDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.changeLanguage', $response['event']);
		$this->assertSame(['globals' => 'translation'], $response['reload']);

		// reload the user freshly
		$user = $this->app->user($user->id());
		$this->assertSame('de', $user->language());
	}
}
