<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GapField::class)]
class GapFieldTest extends TestCase
{
	public function testProps(): void
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
