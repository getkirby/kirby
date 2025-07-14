<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TextDialog::class)]
class TextDialogTest extends TestCase
{
	public function testProps(): void
	{
		$dialog = new TextDialog(
			text: 'A little text'
		);

		$this->assertSame([
			'class'        => null,
			'style'        => null,
			'cancelButton' => null,
			'size'         => 'medium',
			'submitButton' => null,
			'text'         => 'A little text',
		], $dialog->props());
	}

	public function testRender(): void
	{
		$dialog = new TextDialog(
			text: 'A little text'
		);

		$result = $dialog->render();
		$this->assertSame('k-text-dialog', $result['component']);
	}
}
