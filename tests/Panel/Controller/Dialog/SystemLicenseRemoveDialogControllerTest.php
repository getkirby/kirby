<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Dialogs\RemoveDialog;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SystemLicenseRemoveDialogController::class)]
class SystemLicenseRemoveDialogControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Dialog.SystemLicenseRemoveDialogController';

	public function testLoad(): void
	{
		$controller = new SystemLicenseRemoveDialogController();
		$dialog = $controller->load();
		$this->assertInstanceOf(RemoveDialog::class, $dialog);

		$props = $dialog->props();
		$this->assertSame(I18n::translate('license.remove.text'), $props['text']);
		$this->assertSame('medium', $props['size']);
		$this->assertSame('trash', $props['submitButton']['icon']);
		$this->assertSame(I18n::translate('remove'), $props['submitButton']['text']);
		$this->assertSame('negative', $props['submitButton']['theme']);
	}
}
