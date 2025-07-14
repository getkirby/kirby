<?php

namespace Kirby\Panel\Ui\Drawers;

use Kirby\Panel\Ui\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TextDrawer::class)]
class TextDrawerTest extends TestCase
{
	public function testProps(): void
	{
		$drawer = new TextDrawer(
			text: 'A little text'
		);

		$this->assertSame([
			'class'    => null,
			'style'    => null,
			'disabled' => false,
			'icon'     => null,
			'options'  => null,
			'title'    => null,
			'text'     => 'A little text',
		], $drawer->props());
	}

	public function testRender(): void
	{
		$drawer = new TextDrawer();
		$result = $drawer->render();
		$this->assertSame('k-text-drawer', $result['component']);
	}
}
