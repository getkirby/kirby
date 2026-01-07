<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateField::class)]
class TemplateFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = new TemplateField();
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => true,
			'help'        => null,
			'hidden'      => false,
			'icon'        => 'template',
			'label'       => 'Template',
			'name'        => 'template',
			'options'     => [],
			'placeholder' => '—',
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'select',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testLabel(): void
	{
		$field = new TemplateField(
			label: 'Test'
		);

		$this->assertSame('Test', $field->label());
	}

	public function testOptions(): void
	{
		$field = new TemplateField(blueprints: [
			[
				'title' => 'A',
				'name'  => 'a'
			],
			[
				'title' => 'B',
				'name'  => 'b'
			]
		]);

		$expected = [
			[
				'text'  => 'A',
				'value' => 'a'
			],
			[
				'text'  => 'B',
				'value' => 'b'
			]
		];

		$this->assertFalse($field->isDisabled());
		$this->assertSame($expected, $field->options());
	}
}
