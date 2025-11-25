<?php

namespace Kirby\Form\Field;

class HiddenFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('hidden');
		$props = $field->props();

		ksort($props);

		$expected = [
			'default'   => null,
			'hidden'    => true,
			'name'      => 'hidden',
			'saveable'  => true,
			'translate' => true,
			'type'      => 'hidden',
			'when'      => null,
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
