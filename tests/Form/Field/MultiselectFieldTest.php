<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MultiselectField::class)]
class MultiselectFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('multiselect');
		$props = $field->props();

		ksort($props);

		$expected = [
			'accept'    => 'options',
			'autofocus' => false,
			'disabled'  => false,
			'help'      => null,
			'hidden'    => false,
			'icon'      => 'checklist',
			'label'     => 'Multiselect',
			'layout'    => null,
			'max'       => null,
			'min'       => null,
			'name'      => 'multiselect',
			'options'   => [],
			'required'  => false,
			'saveable'  => true,
			'search'    => true,
			'separator' => ',',
			'sort'      => false,
			'required'  => false,
			'translate' => true,
			'type'      => 'multiselect',
			'when'      => null,
			'width'     => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testMin(): void
	{
		$field = $this->field('multiselect', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('multiselect', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}
}
