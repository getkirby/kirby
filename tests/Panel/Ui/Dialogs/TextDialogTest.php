<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialogs\TextDialog
 * @covers ::__construct
 */
class TextDialogTest extends TestCase
{
	/**
	 * @covers ::props
	 */
	public function testProps()
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

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = new TextDialog(
			text: 'A little text'
		);

		$result = $dialog->render();
		$this->assertSame('k-text-dialog', $result['component']);
	}
}
