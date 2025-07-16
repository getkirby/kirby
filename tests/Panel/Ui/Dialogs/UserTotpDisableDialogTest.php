<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Ui\TestCase;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpDisableDialog::class)]
class UserTotpDisableDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserTotpDisableDialog';

	protected function setUp(): void
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
		]);
	}

	public function testFor(): void
	{
		$dialog = UserTotpDisableDialog::for('homer');
		$this->assertInstanceOf(UserTotpDisableDialog::class, $dialog);
		$this->assertSame('homer@simpson.com', $dialog->user->email());
	}

	public function testRender(): void
	{
		// current admin user for themselves
		$dialog = UserTotpDisableDialog::for('test');
		$state  = $dialog->render();
		$this->assertSame('k-form-dialog', $state['component']);

		// current admin user for another user
		$dialog = UserTotpDisableDialog::for('homer');
		$state  = $dialog->render();
		$this->assertSame('k-remove-dialog', $state['component']);

		// non-admin admin user for themselves
		$this->app->clone(['user' => 'homer']);
		$dialog = UserTotpDisableDialog::for('homer');
		$state  = $dialog->render();
		$this->assertSame('k-form-dialog', $state['component']);
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

		$dialog = new UserTotpDisableDialog($user);
		$state  = $dialog->submit();
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

		$dialog = new UserTotpDisableDialog($user);
		$dialog->submit();
	}

	public function testSubmitNonAdminAnotherUser(): void
	{
		$this->expectException(PermissionException::class);

		$this->app->clone(['user' => 'homer']);
		$dialog = UserTotpDisableDialog::for('test');
		$dialog->submit();
	}
}
