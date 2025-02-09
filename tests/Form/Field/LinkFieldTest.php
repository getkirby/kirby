<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;

class LinkFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('link');

		$this->assertSame('link', $field->type());
		$this->assertSame('link', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->label());
		$this->assertNull($field->text());
		$this->assertTrue($field->save());
		$this->assertNull($field->after());
		$this->assertNull($field->before());
		$this->assertNull($field->icon());
		$this->assertNull($field->placeholder());
		$this->assertSame([
			'url',
			'page',
			'file',
			'email',
			'tel',
			'anchor'
		], $field->options());
	}

	public function testOptionsInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid options: foo, bar');

		$this->field('link', [
			'options' => ['page', 'foo', 'bar']
		]);
	}
}
