<?php

namespace Kirby\Panel\Ui;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Dialog::class)]
class DialogTest extends TestCase
{
	public function testProps(): void
	{
		$dialog = new Dialog(
			class:        'k-my-dialog',
			size:         'large',
			submitButton: 'Confirm'
		);

		$this->assertSame([
			'class'        => 'k-my-dialog',
			'style'        => null,
			'cancelButton' => null,
			'size'         => 'large',
			'submitButton' => 'Confirm'
		], $dialog->props());
	}

	public function testRender(): void
	{
		$dialog = new Dialog(
			component: 'k-test',
		);

		$result = $dialog->render();
		$this->assertSame('k-test', $result['component']);
	}
}
