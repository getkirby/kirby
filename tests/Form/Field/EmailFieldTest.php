<?php

namespace Kirby\Form\Field;

class EmailFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('email');

		$this->assertSame('email', $field->type());
		$this->assertSame('email', $field->name());
		$this->assertSame('', $field->value());
		$this->assertSame('email', $field->icon());
		$this->assertSame('mail@example.com', $field->placeholder());
		$this->assertNull($field->counter());
		$this->assertSame('email', $field->autocomplete());
		$this->assertTrue($field->save());
	}

	public function testEmailValidation()
	{
		$field = $this->field('email', [
			'value' => 'mail@getkirby.com'
		]);

		$this->assertTrue($field->isValid());

		$field = $this->field('email', [
			'value' => 'mail[at]getkirby.com'
		]);

		$this->assertFalse($field->isValid());
	}

	public function testMinLength()
	{
		$field = $this->field('email', [
			'value' => 'mail@test.com',
			'minlength' => 14
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testMaxLength()
	{
		$field = $this->field('email', [
			'value'     => 'mail@test.com',
			'maxlength' => 12
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}
}
