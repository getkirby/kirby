<?php

namespace Kirby\Form\Field;

class GapFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('gap');
		$props = $field->props();

		ksort($props);

		$expected = [
			'hidden'   => false,
			'name'     => 'gap',
			'saveable' => false,
			'type'     => 'gap',
			'when'     => null,
			'width'    => '1/1',
		];

		$this->assertSame($expected, $props);
	}
}
