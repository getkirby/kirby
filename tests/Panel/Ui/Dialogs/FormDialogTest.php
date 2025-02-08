<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FormDialog::class)]
class FormDialogTest extends TestCase
{
	public function testProps(): void
	{
		$dialog = new FormDialog(
			fields: $fields = [
				'a' => [
					'type' => 'text'
				]
			],
			value: $value = [
				'a' => 'foo'
			]
		);

		$this->assertSame([
			'class'        => null,
			'style'        => null,
			'cancelButton' => null,
			'size'         => 'medium',
			'submitButton' => null,
			'fields'       => $fields,
			'text'         => null,
			'value'        => $value
		], $dialog->props());
	}

	public function testRender(): void
	{
		$dialog = new FormDialog();
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}
}
