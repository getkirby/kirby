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
	public function testButtons(): void
	{
		$field = $this->field('buttons', [
			'buttons' => [['text' => 'Button A']]
		]);

		$this->assertSame('Button A', $field->buttons()[0]['text']);

		// as query
		$field = $this->field('buttons', [
			'model'   => new MockPageForButtonsField(['slug' => 'test']),
			'buttons' => 'page.buttons'
		]);

		$this->assertSame('Button A', $field->buttons()[0]['text']);

		// with string templates
		$field = $this->field('buttons', [
			'buttons' => [['text' => 'Button {{ 1 + 2 }} {{ page.slug }}']]
		]);

		$this->assertSame('Button 3 test', $field->buttons()[0]['text']);
	}

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
}
