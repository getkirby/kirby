<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InfoField::class)]
class InfoFieldTest extends TestCase
{
	public function testIcon(): void
	{
		$field = $this->field('info', ['icon' => 'heart']);
		$this->assertSame('heart', $field->icon());
	}

	public function testProps(): void
	{
		$field = $this->field('info');
		$props = $field->props();

		ksort($props);

		$expected = [
			'help'     => null,
			'hidden'   => false,
			'icon'     => null,
			'label'    => 'Info',
			'name'     => 'info',
			'saveable' => false,
			'text'     => null,
			'theme'    => null,
			'type'     => 'info',
			'when'     => null,
			'width'    => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testText(): void
	{
		// simple text
		$field = $this->field('info', [
			'text' => 'test'
		]);

		$this->assertSame('<p>test</p>', $field->text());

		// translated text
		$field = $this->field('info', [
			'text' => [
				'en' => 'en',
				'de' => 'de'
			]
		]);

		$this->assertSame('<p>en</p>', $field->text());

		// text template
		$field = $this->field('info', [
			'text' => '{{ page.title }}',
			'model' => new Page([
				'slug'    => 'test',
				'content' => [
					'title' => 'Test'
				]
			])
		]);

		$this->assertSame('<p>Test</p>', $field->text());
	}

	public function testTheme(): void
	{
		$field = $this->field('info', ['theme' => 'positive']);
		$this->assertSame('positive', $field->theme());
	}
}
