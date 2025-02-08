<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\License;
use Kirby\Cms\LicenseStatus;
use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class SystemValidLicenseDialog extends SystemLicenseDialog
{
	public function isRenewable(): bool
	{
		return false;
	}
}

#[CoversClass(SystemLicenseDialog::class)]
class SystemLicenseDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.SystemLicenseDialog';

	public function testProps(): void
	{
		$dialog = new SystemLicenseDialog();
		$props  = $dialog->props();
		$this->assertSame('No valid license', $props['license']['info']);
		$this->assertSame('Renew', $props['submitButton']['text']);
	}

	public function testPropsNonRenewable(): void
	{
		$license = $this->createStub(License::class);
		$license->method('status')->willReturn(LicenseStatus::Active);
		$license->method('renewal')->willReturn('2025-01-01');

		$dialog = new SystemValidLicenseDialog($license);
		$props  = $dialog->props();
		$this->assertSame('Includes new major versions until 2025-01-01', $props['license']['info']);
		$this->assertFalse($props['submitButton']);
	}

	public function testRender(): void
	{
		$dialog = new SystemLicenseDialog();
		$result = $dialog->render();
		$this->assertSame('k-license-dialog', $result['component']);
	}
}
