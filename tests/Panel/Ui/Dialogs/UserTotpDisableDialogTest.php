<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpDisableDialog::class)]
class UserTotpDisableDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.UserTotpDisableDialog';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
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

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];
	}

	public function testConstruct(): void
	{
		$dialog = new UserTotpDisableDialog();
		$this->assertSame($this->app->user(), $dialog->user);
		$this->assertSame('test@getkirby.com', $dialog->user->email());

		$dialog = new UserTotpDisableDialog('homer');
		$this->assertSame('homer@simpson.com', $dialog->user->email());
	}

	public function testLoad(): void
	{
		// current admin user for themselves
		$dialog = new UserTotpDisableDialog();
		$state  = $dialog->load();
		$this->assertSame('k-form-dialog', $state['component']);

		// current admin user for another user
		$dialog = new UserTotpDisableDialog('homer');
		$state  = $dialog->load();
		$this->assertSame('k-remove-dialog', $state['component']);

		// non-admin admin user for themselves
		$this->app->clone(['user' => 'homer']);
		$dialog = new UserTotpDisableDialog();
		$state  = $dialog->load();
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

		$dialog = new UserTotpDisableDialog();
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

		$dialog = new UserTotpDisableDialog();
		$dialog->submit();
	}

	public function testSubmitNonAdminAnotherUser(): void
	{
		$this->expectException(PermissionException::class);

		$this->app->clone(['user' => 'homer']);
		$dialog = new UserTotpDisableDialog('test');
		$dialog->submit();
	}
}
