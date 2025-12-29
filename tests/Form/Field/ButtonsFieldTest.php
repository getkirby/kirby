<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

class MockPageForButtonsField extends Page
{
	public function buttons(): array
	{
		return [
			['text' => 'Button A']
		];
	}
}

#[CoversClass(ButtonsField::class)]
class ButtonsFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = $this->field('buttons');

		$this->assertSame('buttons', $field->type());
		$this->assertSame('buttons', $field->name());
		$this->assertSame('Buttons', $field->label());
		$this->assertSame([], $field->buttons());
		$this->assertFalse($field->hasValue());

		$props = $field->props();
		$this->assertSame('Buttons', $props['label']);
		$this->assertSame('buttons', $props['name']);
		$this->assertSame('buttons', $props['type']);
		$this->assertSame([], $props['buttons']);
	}

	public function testButtons(): void
	{
		$field = $this->field('buttons', [
			'buttons' => $expected = [['text' => 'Button A']]
		]);

		$this->assertSame($expected, $field->buttons());

		// as query
		$field = $this->field('buttons', [
			'model'   => new MockPageForButtonsField(['slug' => 'test']),
			'buttons' => 'page.buttons'
		]);

		$this->assertSame($expected, $field->buttons());
	}
}
