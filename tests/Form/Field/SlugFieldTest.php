<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SlugField::class)]
class SlugFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = $this->field('slug');
		$props = $field->props();

		ksort($props);

		$expected = [
			'after'        => null,
			'allow'        => null,
			'autocomplete' => null,
			'autofocus'    => false,
			'before'       => null,
			'converter'    => null,
			'counter'      => false,
			'disabled'     => false,
			'font'         => 'sans-serif',
			'help'         => null,
			'hidden'       => false,
			'icon'         => 'url',
			'label'        => 'URL appendix',
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'slug',
			'path'         => null,
			'pattern'      => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'sync'         => null,
			'translate'    => true,
			'type'         => 'slug',
			'when'         => null,
			'width'        => '1/1',
			'wizard'       => false,
		];

		$this->assertSame($expected, $props);
	}

	public function testLabel(): void
	{
		$field = $this->field('slug', [
			'label' => 'Test'
		]);

		$this->assertSame('Test', $field->label());
	}

}
