<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SystemActivateDialog::class)]
class SystemActivateDialogTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.Dialogs.SystemActivateDialog';

	public function testProps(): void
	{
		$dialog = new SystemActivateDialog();
		$props  = $dialog->props();
		$this->assertArrayHasKey('domain', $props['fields']);
		$this->assertArrayHasKey('license', $props['fields']);
		$this->assertArrayHasKey('email', $props['fields']);
		$this->assertSame('Activate', $props['submitButton']['text']);
		$this->assertNull($props['value']['license']);
		$this->assertNull($props['value']['email']);
	}

	public function testRender(): void
	{
		$dialog = new SystemActivateDialog();
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}
}
