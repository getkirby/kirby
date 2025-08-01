<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialogs\FormDialog;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SystemLicenseActivateDialogController::class)]
class SystemLicenseActivateDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.SystemLicenseActivateDialogController';

	public function testLoad(): void
	{
		$controller = new SystemLicenseActivateDialogController();
		$dialog = $controller->load();
		$this->assertInstanceOf(FormDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertArrayHasKey('domain', $props['fields']);
		$this->assertArrayHasKey('license', $props['fields']);
		$this->assertArrayHasKey('email', $props['fields']);
		$this->assertSame('Activate', $props['submitButton']['text']);
		$this->assertNull($props['value']['license']);
		$this->assertNull($props['value']['email']);
	}

	public function testSubmitWithInvalidLicense(): void
	{
		$this->setRequest([
			'license' => 'K2-1234'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid license code');

		$controller = new SystemLicenseActivateDialogController();
		$controller->submit();
	}

	public function testSubmitWithInvalidEmail(): void
	{
		$this->setRequest([
			'license' => 'K3-PRO-' . Str::random(32),
			'email'   => 'mail@'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid email address');

		$controller = new SystemLicenseActivateDialogController();
		$controller->submit();
	}
}
