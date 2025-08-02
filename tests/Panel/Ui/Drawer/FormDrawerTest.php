<?php

namespace Kirby\Panel\Ui\Drawer;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FormDrawer::class)]
class FormDrawerTest extends TestCase
{
	public function testProps(): void
	{
		$drawer = new FormDrawer(
			empty: 'No form',
			fields: $fields = [
				'title' => [
					'type' => 'text',
					'label' => 'Title',
				],
			],
			value: $value = [
				'title' => 'A little text'
			]
		);

		$this->assertSame([
			'class'    => null,
			'style'    => null,
			'icon'     => null,
			'options'  => null,
			'title'    => null,
			'disabled' => false,
			'empty'    => 'No form',
			'fields'   => $fields,
			'value'    => $value,
		], $drawer->props());
	}

	public function testRender(): void
	{
		$drawer = new FormDrawer();
		$result = $drawer->render();
		$this->assertSame('k-form-drawer', $result['component']);
	}
}
