<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\License;
use Kirby\Cms\LicenseStatus;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialog;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SystemLicenseDialogController::class)]
class SystemLicenseDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.SystemLicenseDialogController';

	public function testLoad(): void
	{
		$controller = new SystemLicenseDialogController();
		$dialog     = $controller->load();
		$this->assertInstanceOf(Dialog::class, $dialog);
		$this->assertSame('k-license-dialog', $dialog->component);

		$props = $dialog->props();
		$this->assertSame('Renew', $props['submitButton']['text']);
	}

	public function testLoadNonRenewable(): void
	{
		$license = $this->createStub(License::class);
		$license->method('status')->willReturn(LicenseStatus::Active);
		$license->method('renewal')->willReturn('2999-01-01');

		$controller = new SystemLicenseDialogController($license);
		$dialog     = $controller->load();
		$props      = $dialog->props();
		$this->assertFalse($props['submitButton']);
	}

	public function testLicense(): void
	{
		$controller = new SystemLicenseDialogController();
		$this->assertTrue($controller->isRenewable());

		$license = $controller->license();
		$this->assertSame('No valid license', $license['info']);
	}

	public function testLicenseNonRenewable(): void
	{
		$license = $this->createStub(License::class);
		$license->method('status')->willReturn(LicenseStatus::Active);
		$license->method('renewal')->willReturn('2999-01-01');

		$controller = new SystemLicenseDialogController($license);
		$this->assertFalse($controller->isRenewable());

		$license = $controller->license();
		$this->assertSame('Includes new major versions until 2999-01-01', $license['info']);
	}
}
