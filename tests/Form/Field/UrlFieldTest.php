<?php

namespace Kirby\Form\Field;

class UrlFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('url');

		$this->assertSame('url', $field->type());
		$this->assertSame('url', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('url', $field->icon());
		$this->assertSame('https://example.com', $field->placeholder());
		$this->assertNull($field->counter());
		$this->assertSame('url', $field->autocomplete());
		$this->assertTrue($field->save());
	}

	public function testUrlValidation()
	{
		$field = $this->field('url', [
			'value' => 'https://getkirby.com'
		]);

		$this->assertTrue($field->isValid());

		$field = $this->field('url', [
			'value' => 'getkirby.com'
		]);

		$this->assertFalse($field->isValid());
	}

	public function testMinLength()
	{
		$field = $this->field('url', [
			'value' => 'https://test.com',
			'minlength' => 17
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength()
	{
		$field = $this->field('url', [
			'value'     => 'https://test.com',
			'maxlength' => 15
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}
}
