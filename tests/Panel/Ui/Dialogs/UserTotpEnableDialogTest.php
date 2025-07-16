<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
use Kirby\Panel\Ui\TestCase;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpEnableDialog::class)]
class UserTotpEnableDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.UserTotpEnableDialog';

	public function testConstruct(): void
	{
		$dialog = new UserTotpEnableDialog();
		$this->assertSame($this->app->user(), $dialog->user);
		$this->assertSame('test@getkirby.com', $dialog->user->email());
	}

	public function testQr(): void
	{
		$dialog = new UserTotpEnableDialog();
		$qr     = $dialog->qr();

		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertStringContainsString('otpauth://totp/:test%40getkirby.com?secret=', $qr->data);
	}

	public function testRender(): void
	{
		$dialog = new UserTotpEnableDialog();
		$state  = $dialog->render();

		$this->assertSame('k-totp-dialog', $state['component']);
		$this->assertIsString($state['props']['value']['secret']);
		$this->assertIsString($state['props']['qr']);
	}

	public function testSubmit(): void
	{
		$totp            = new Totp();
		$_GET['secret']  = $secret = $totp->secret();
		$_GET['confirm'] = $totp->generate();

		$user   = $this->app->user();
		$dialog = new UserTotpEnableDialog();
		$this->assertNull($user->secret('totp'));

		$state = $dialog->submit();
		$this->assertSame($secret, $user->secret('totp'));
		$this->assertIsString($state['message']);
	}

	public function testSubmitNoConfirmCode(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.missing');

		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'secret'  => 'foo',
					'confirm' => null
				]
			]
		]);

		$dialog = new UserTotpEnableDialog();
		$dialog->submit();
	}

	public function testSubmitWrongConfirmCode(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.invalid');

		$totp      = new Totp();
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'secret'  => $totp->secret(),
					'confirm' => 'bar'
				]
			]
		]);

		$dialog = new UserTotpEnableDialog();
		$dialog->submit();
	}
}
