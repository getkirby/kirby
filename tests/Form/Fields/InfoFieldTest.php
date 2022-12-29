<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;

class InfoFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('info');

		$this->assertSame('info', $field->type());
		$this->assertSame('info', $field->name());
		$this->assertSame(null, $field->value());
		$this->assertSame(null, $field->label());
		$this->assertSame(null, $field->text());
		$this->assertFalse($field->save());
	}

	public function testText()
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
}
