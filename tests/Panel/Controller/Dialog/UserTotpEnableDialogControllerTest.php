<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpEnableDialogController::class)]
class UserTotpEnableDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.UserTotpEnableDialogController';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
				]
			],
			'user' => 'test@getkirby.com'
		]);
	}

	public function testConstruct(): void
	{
		$dialog = new UserTotpEnableDialogController();
		$this->assertSame($this->app->user(), $dialog->user);
		$this->assertSame('test@getkirby.com', $dialog->user->email());
	}

	public function testLoad(): void
	{
		$controller = new UserTotpEnableDialogController();
		$dialog     = $controller->load();

		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-totp-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertIsString($props['value']['secret']);
		$this->assertIsString($props['qr']);
	}

	public function testQr(): void
	{
		$controller = new UserTotpEnableDialogController();
		$qr         = $controller->qr();

		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertStringContainsString('otpauth://totp/:test%40getkirby.com?secret=', $qr->data);
	}

	public function testSubmit(): void
	{
		$totp            = new Totp();
		$_GET['secret']  = $secret = $totp->secret();
		$_GET['confirm'] = $totp->generate();

		$user       = $this->app->user();
		$controller = new UserTotpEnableDialogController();
		$this->assertNull($user->secret('totp'));

		$state  = $controller->submit();
		$this->assertSame($secret, $user->secret('totp'));
		$this->assertIsString($state['message']);
	}

	public function testSubmitNoConfirmCode(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.missing');

		$_GET['secret']  = 'foo';
		$_GET['confirm'] = null;

		$controller = new UserTotpEnableDialogController();
		$controller->submit();
	}

	public function testSubmitWrongConfirmCode(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.invalid');

		$totp            = new Totp();
		$_GET['secret']  = $totp->secret();
		$_GET['confirm'] = 'bar';

		$controller = new UserTotpEnableDialogController();
		$controller->submit();
	}
}
