<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialogs\FormDialog
 * @covers ::__construct
 */
class FormDialogTest extends TestCase
{
	/**
	 * @covers ::props
	 */
	public function testProps()
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

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = new FormDialog();
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}
}
