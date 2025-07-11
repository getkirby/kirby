<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Image\QrCode;
use Kirby\TestCase;
use Kirby\Toolkit\Totp;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserTotpEnableDialog::class)]
class UserTotpEnableDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.UserTotpEnableDialog';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
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
		$dialog = new UserTotpEnableDialog();

		$this->assertSame($this->app->user(), $dialog->user);
		$this->assertSame('test@getkirby.com', $dialog->user->email());
	}

	public function testLoad(): void
	{
		$dialog = new UserTotpEnableDialog();
		$state  = $dialog->load();

		$this->assertSame('k-totp-dialog', $state['component']);
		$this->assertIsString($state['props']['value']['secret']);
		$this->assertIsString($state['props']['qr']);
	}

	public function testQr(): void
	{
		$dialog = new UserTotpEnableDialog();
		$qr     = $dialog->qr();

		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertStringContainsString('otpauth://totp/:test%40getkirby.com?secret=', $qr->data);
	}

	public function testSubmit(): void
	{
		$totp            = new Totp();
		$_GET['secret']  = $secret = $totp->secret();
		$_GET['confirm'] = $totp->generate();

		$user   = $this->app->user();
		$dialog = new UserTotpEnableDialog();
		$this->assertNull($user->secret('totp'));

		$state  = $dialog->submit();
		$this->assertSame($secret, $user->secret('totp'));
		$this->assertIsString($state['message']);
	}

	public function testSubmitNoConfirmCode(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.missing');

		$_GET['secret']  = 'foo';
		$_GET['confirm'] = null;

		$dialog = new UserTotpEnableDialog();
		$dialog->submit();
	}

	public function testSubmitWrongConfirmCode(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.login.totp.confirm.invalid');

		$totp            = new Totp();
		$_GET['secret']  = $totp->secret();
		$_GET['confirm'] = 'bar';

		$dialog = new UserTotpEnableDialog();
		$dialog->submit();
	}
}
