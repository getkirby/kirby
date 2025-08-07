<?php

namespace Kirby\Panel\Ui\Dialog;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ErrorDialog::class)]
class ErrorDialogTest extends TestCase
{
	public function testProps(): void
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

	public function testRender(): void
	{
		$dialog = new ErrorDialog(
			message: 'A little error',
		);

		$result = $dialog->render();
		$this->assertSame('k-error-dialog', $result['component']);
	}
}
