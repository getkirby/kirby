<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MultiselectField::class)]
class MultiselectFieldTest extends TestCase
{
	public function testMax(): void
	{
		$field = $this->field('multiselect', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertSame(1, $field->max());
		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testMin(): void
	{
		$field = $this->field('multiselect', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertSame(2, $field->min());
		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testNumericOptions(): void
	{
		$field = $this->field('multiselect', [
			'value'   => '36, 40',
			'options' => [36, 38, 40]
		]);

		$this->assertTrue($field->isValid());

		$field->fill('36, 42');

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('accepted', $field->errors());
	}

	public function testOptionsQueryWithProtectedPages(): void
	{
		$app   = $this->appWithProtectedPages();
		$field = $this->field('multiselect', [
			'model'   => $app->site(),
			'options' => 'site.children'
		]);

		$field->fill('a, b');

		// the stored page that must not be listed is kept
		// as placeholder, the other one is hidden
		$this->assertSame(['a', 'b'], array_column($field->options(), 'value'));
		$this->assertSame('[unknown]', $field->options()[1]['text']);
		$this->assertTrue($field->isValid());
		$this->assertSame('a, b', $field->toStoredValue());
	}

	public function testProps(): void
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
}
