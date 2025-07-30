<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoveDialog::class)]
class RemoveDialogTest extends TestCase
{
	public function testProps(): void
	{
		$dialog = new RemoveDialog(
			text: 'A little text'
		);

		$this->assertSame([
			'class'        => null,
			'style'        => null,
			'cancelButton' => null,
			'size'         => 'medium',
			'submitButton' => [
				'icon'  => 'trash',
				'theme' => 'negative'
			],
			'text'         => 'A little text',
		], $dialog->props());
	}

	public function testRender(): void
	{
		$dialog = new RemoveDialog();
		$result = $dialog->render();
		$this->assertSame('k-remove-dialog', $result['component']);
	}
}
