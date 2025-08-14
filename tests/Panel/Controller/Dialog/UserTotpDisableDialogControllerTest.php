<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpDisableDialogController::class)]
class UserTotpDisableDialogControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserTotpDisableDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				],
				[
					'id'    => 'homer',
					'email' => 'homer@simpson.com',
				]
			],
			'user' => 'test@getkirby.com'
		]);
	}

	public function testFactory(): void
	{
		$dialog = UserTotpDisableDialogController::factory();
		$this->assertInstanceOf(UserTotpDisableDialogController::class, $dialog);
		$this->assertSame($this->app->user(), $dialog->user);
		$this->assertSame('test@getkirby.com', $dialog->user->email());

		$dialog = UserTotpDisableDialogController::factory('homer');
		$this->assertInstanceOf(UserTotpDisableDialogController::class, $dialog);
		$this->assertSame('homer@simpson.com', $dialog->user->email());
	}

	public function testLoad(): void
	{
		// current admin user for themselves
		$controller = UserTotpDisableDialogController::factory();
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		// current admin user for another user
		$controller = UserTotpDisableDialogController::factory('homer');
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		// non-admin admin user for themselves
		$this->app->clone(['user' => 'homer']);
		$controller = UserTotpDisableDialogController::factory();
		$dialog     = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);
	}

	public function testSubmit(): void
	{
		$user     = $this->app->user();
		$totp     = new Totp();
		$secret   = $totp->secret();
		$password = 'foobar123';
		$user->changeTotp($secret);
		$user->changePassword($password);

		$_GET['password'] = $password;
		$this->assertSame($secret, $user->secret('totp'));

		$controller = UserTotpDisableDialogController::factory();
		$state      = $controller->submit();
		$this->assertNull($user->secret('totp'));
		$this->assertIsString($state['message']);
	}

	public function testSubmitWrongPassword(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.user.password.wrong');

		$user     = $this->app->user();
		$password = 'foobar123';
		$user->changePassword($password);

		$_GET['password'] = 'nonono123';

		$controller = UserTotpDisableDialogController::factory();
		$controller->submit();
	}

	public function testSubmitNonAdminAnotherUser(): void
	{
		$this->expectException(PermissionException::class);

		$this->app->clone(['user' => 'homer']);
		$controller = UserTotpDisableDialogController::factory('test');
		$controller->submit();
	}
}
