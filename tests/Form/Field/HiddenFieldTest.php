<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HiddenField::class)]
class HiddenFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('hidden');
		$props = $field->props();

		ksort($props);

		$expected = [
			'hidden'   => true,
			'name'     => 'hidden',
			'saveable' => true,
			'type'     => 'hidden',
		];

		$this->assertSame($expected, $props);
	}

	public function testFill(): void
	{
		$field = $this->field('hidden');
		$field->fill('test');

		$this->assertSame('test', $field->toFormValue());
	}
}
