<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserDialogController::class)]
#[CoversClass(UserChangePasswordDialogController::class)]
class UserChangePasswordDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserChangePasswordDialogController';


	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'password' => User::hashPassword('12345678'),
					'role'     => 'admin',
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	public function testFactory(): void
	{
		$controller = UserChangePasswordDialogController::factory('test');
		$this->assertInstanceOf(UserChangePasswordDialogController::class, $controller);
		$this->assertSame('test', $controller->user->id());
	}

	public function testLoad(): void
	{
		$user       = $this->app->user('test');
		$controller = new UserChangePasswordDialogController($user);
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame('Your current password', $props['fields']['currentPassword']['label']);
		$this->assertSame('New password', $props['fields']['password']['label']);
		$this->assertSame('Confirm the new password…', $props['fields']['passwordConfirmation']['label']);
		$this->assertSame('Change', $props['submitButton']);
	}

	public function testSubmit(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '12345678',
					'password'             => 'abcdefgh',
					'passwordConfirmation' => 'abcdefgh'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$user = $this->app->user('test');
		$this->assertTrue($user->validatePassword('12345678'));

		$controller = new UserChangePasswordDialogController($user);
		$response   = $controller->submit();

		$this->assertSame('user.changePassword', $response['event']);

		// reload the user freshly
		$user = $this->app->user($user->id());
		$this->assertTrue($user->validatePassword('abcdefgh'));
	}

	public function testSubmitWithInvalidCurrentPassword(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '123456',
					'password'             => 'abcdefgh',
					'passwordConfirmation' => 'abcdefgh'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.wrong');

		$user       = $this->app->user('test');
		$controller = new UserChangePasswordDialogController($user);
		$controller->submit();
	}

	public function testChangePasswordOnSubmitWithInvalidPassword(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '12345678',
					'password'             => 'abcdef',
					'passwordConfirmation' => 'abcdef'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.invalid');

		$user       = $this->app->user('test');
		$controller = new UserChangePasswordDialogController($user);
		$controller->submit();
	}

	public function testChangePasswordOnSubmitWithInvalidConfirmation(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'currentPassword'      => '12345678',
					'password'             => 'abcdefgh',
					'passwordConfirmation' => 'abcdefg'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.notSame');

		$user       = $this->app->user('test');
		$controller = new UserChangePasswordDialogController($user);
		$controller->submit();
	}
}
