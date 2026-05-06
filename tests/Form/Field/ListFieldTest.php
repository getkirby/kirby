<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListField::class)]
class ListFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = $this->field('list');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'    => false,
			'disabled'     => false,
			'help'         => null,
			'hidden'       => false,
			'icon'         => null,
			'label'        => 'List',
			'marks'        => null,
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'list',
			'nodes'        => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'toolbar'      => null,
			'translate'    => true,
			'type'         => 'list',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testToFormValueSanitized(): void
	{
		$field = $this->field('list', [
			'value' => '<ul><li>Item <strong>one</strong></li></ul><script>alert("Hacked")</script>'
		]);

		$this->assertSame('<ul><li>Item <strong>one</strong></li></ul>', $field->toFormValue());
	}

	public function testValueSanitized(): void
	{
		$field = $this->field('list', [
			'value' => '<ul><li>Item <strong>one</strong></li></ul><script>alert("Hacked")</script>'
		]);

		$this->assertSame('<ul><li>Item <strong>one</strong></li></ul>', $field->value());
	}
}
