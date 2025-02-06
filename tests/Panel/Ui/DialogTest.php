<?php

namespace Kirby\Panel\Ui;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Dialog
 * @covers ::__construct
 */
class DialogTest extends TestCase
{
	/**
	 * @covers ::props
	 */
	public function testProps()
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

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$dialog = new Dialog(
			component: 'k-test',
		);

		$result = $dialog->render();
		$this->assertSame('k-test', $result['component']);
	}
}
