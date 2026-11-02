<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TogglesField::class)]
class TogglesFieldTest extends TestCase
{
	public function testGrow(): void
	{
		$field = $this->field('toggles', [
			'grow' => false
		]);

		$this->assertFalse($field->grow());

		$field = $this->field('toggles', [
			'grow' => true
		]);

		$this->assertTrue($field->grow());
	}

	public function testIsResettable(): void
	{
		$field = $this->field('toggles', [
			'reset' => true
		]);

		$this->assertTrue($field->isResettable());

		$field = $this->field('toggles', [
			'options' => ['a', 'b'],
			'reset'   => false,
			'value'   => 'a'
		]);

		$this->assertFalse($field->isResettable());

		// exporting the prop must not call `ValueField::reset()`
		$this->assertFalse($field->props()['reset']);
		$this->assertSame('a', $field->toFormValue());
	}

	public function testLabels(): void
	{
		$field = $this->field('toggles', [
			'labels' => false
		]);

		$this->assertFalse($field->labels());

		$field = $this->field('toggles', [
			'labels' => true
		]);

		$this->assertTrue($field->labels());
	}

	public function testProps(): void
	{
		$field = $this->field('toggles');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'  => false,
			'disabled'   => false,
			'grow'       => true,
			'help'       => null,
			'hidden'     => false,
			'label'      => 'Toggles',
			'labels'     => true,
			'name'       => 'toggles',
			'options'    => [],
			'required'   => false,
			'reset'      => true,
			'saveable'   => true,
			'translate'  => true,
			'type'       => 'toggles',
			'when'       => null,
			'width'      => '1/1',
		];

		$this->assertSame($expected, $props);
	}
}
