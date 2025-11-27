<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineField::class)]
class LineFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('line');
		$props = $field->props();

		ksort($props);

		$expected = [
			'hidden'   => false,
			'name'     => 'line',
			'saveable' => false,
			'type'     => 'line',
			'when'     => null,
			'width'    => '1/1',
		];

		$this->assertSame($expected, $props);
	}
}
