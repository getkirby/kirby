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
			'resettable' => true,
			'saveable'   => true,
			'translate'  => true,
			'type'       => 'toggles',
			'when'       => null,
			'width'      => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testResettable(): void
	{
		$field = $this->field('toggles', [
			'resettable' => false
		]);

		$this->assertFalse($field->resettable());

		$field = $this->field('toggles', [
			'resettable' => true
		]);

		$this->assertTrue($field->resettable());
	}
}
