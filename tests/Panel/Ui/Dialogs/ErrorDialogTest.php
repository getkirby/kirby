<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialogs\ErrorDialog
 * @covers ::__construct
 */
class ErrorDialogTest extends TestCase
{
	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$dialog = new ErrorDialog(
			message: 'A little error',
			details: ['file' => 'foo.php']
		);

		$this->assertSame([
			'class'        => null,
			'style'        => null,
			'cancelButton' => false,
			'size'         => 'medium',
			'submitButton' => false,
			'details'      => ['file' => 'foo.php'],
			'message'      => 'A little error',
		], $dialog->props());
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = new ErrorDialog(
			message: 'A little error',
		);

		$result = $dialog->render();
		$this->assertSame('k-error-dialog', $result['component']);
	}
}
