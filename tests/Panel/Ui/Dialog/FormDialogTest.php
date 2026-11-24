<?php

namespace Kirby\Panel\Ui\Dialog;

use Kirby\Panel\TestCase;
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

		$this->assertEquals([
			'class'        => null,
			'style'        => null,
			'cancelButton' => null,
			'size'         => 'medium',
			'submitButton' => null,
			'text'         => null,
			'fields'       => $fields,
			'value'        => (object)$value
		], $dialog->props());
	}

	public function testPropsWithoutValue(): void
	{
		$dialog = new FormDialog();
		$this->assertSame('{}', json_encode($dialog->props()['value']));
	}

	public function testRender(): void
	{
		$dialog = new FormDialog();
		$result = $dialog->render();
		$this->assertSame('k-form-dialog', $result['component']);
	}
}
