<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HiddenField::class)]
class HiddenFieldTest extends TestCase
{
	public function testFill(): void
	{
		$field = $this->field('hidden');
		$field->fill('test');

		$this->assertSame('test', $field->toFormValue());
	}

	public function testIsHidden(): void
	{
		$field = $this->field('hidden');
		$this->assertTrue($field->isHidden());
	}

	public function testProps(): void
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

	public function testReset(): void
	{
		$field = $this->field('hidden');
		$field->fill('test');
		$this->assertSame('test', $field->toFormValue());

		$field->reset();
		$this->assertNull($field->toFormValue());
	}
}
