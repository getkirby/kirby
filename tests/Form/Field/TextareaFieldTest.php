<?php

namespace Kirby\Form\Field;

class TextareaFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('textarea');

		$this->assertSame('textarea', $field->type());
		$this->assertSame('textarea', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->icon());
		$this->assertNull($field->placeholder());
		$this->assertTrue($field->counter());
		$this->assertNull($field->maxlength());
		$this->assertNull($field->minlength());
		$this->assertNull($field->size());
		$this->assertSame([], $field->files());
		$this->assertSame(['accept' => '*'], $field->uploads());
		$this->assertTrue($field->save());
	}

	public function testButtonsDisabled()
	{
		$field = $this->field('textarea', [
			'buttons' => false
		]);

		$this->assertFalse($field->buttons());
	}

	public function testButtonsArray()
	{
		$field = $this->field('textarea', [
			'buttons' => [
				'bold',
				'italic'
			]
		]);

		$this->assertSame(['bold', 'italic'], $field->buttons());
	}

	public function testDefaultTrimmed()
	{
		$field = $this->field('textarea', [
			'default' => 'test '
		]);

		$this->assertSame('test', $field->default());
	}

	public function testFiles()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'files' => [
				'query' => 'page.images'
			]
		]);

		$this->assertSame(['query' => 'page.images'], $field->files());
	}

	public function testFilesQuery()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'files' => 'page.images'
		]);

		$this->assertSame(['query' => 'page.images'], $field->files());
	}

	public function testFilesWithInvalidInput()
	{
		$field = $this->field('textarea', [
			'files' => 1
		]);

		$this->assertSame([], $field->files());
	}

	public function testMaxLength()
	{
		$field = $this->field('textarea', [
			'value'     => 'test',
			'maxlength' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}

	public function testMinLength()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'minlength' => 5
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testUploads()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'template' => 'test'
			]
		]);

		$this->assertSame(['template' => 'test', 'accept' => '*'], $field->uploads());
	}

	public function testUploadsDisabled()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => false,
		]);

		$this->assertFalse($field->uploads());
	}

	public function testUploadsParent()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'parent' => 'page.parent'
			]
		]);

		$this->assertSame(['parent' => 'page.parent', 'accept' => '*'], $field->uploads());
	}

	public function testUploadsTemplate()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => 'test'
		]);

		$this->assertSame(['template' => 'test', 'accept' => '*'], $field->uploads());
	}

	public function testUploadsWithInvalidInput()
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => 1,
		]);

		$this->assertSame(['accept' => '*'], $field->uploads());
	}

	public function testValueTrimmed()
	{
		$field = $this->field('textarea', [
			'value' => 'test '
		]);

		$this->assertSame('test', $field->value());
	}
}
