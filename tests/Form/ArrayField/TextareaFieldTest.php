<?php

namespace Kirby\Form\ArrayField;

class TextareaFieldTest extends TestCase
{
	public function testDefaultProps(): void
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

	public function testButtonsDisabled(): void
	{
		$field = $this->field('textarea', [
			'buttons' => false
		]);

		$this->assertFalse($field->buttons());
	}

	public function testButtonsArray(): void
	{
		$field = $this->field('textarea', [
			'buttons' => [
				'bold',
				'italic'
			]
		]);

		$this->assertSame(['bold', 'italic'], $field->buttons());
	}

	public function testDefaultTrimmed(): void
	{
		$field = $this->field('textarea', [
			'default' => 'test '
		]);

		$this->assertSame('test', $field->default());
	}

	public function testFiles(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'files' => [
				'query' => 'page.images'
			]
		]);

		$this->assertSame(['query' => 'page.images'], $field->files());
	}

	public function testFilesQuery(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'files' => 'page.images'
		]);

		$this->assertSame(['query' => 'page.images'], $field->files());
	}

	public function testFilesWithInvalidInput(): void
	{
		$field = $this->field('textarea', [
			'files' => 1
		]);

		$this->assertSame([], $field->files());
	}

	public function testMaxLength(): void
	{
		$field = $this->field('textarea', [
			'value'     => 'test',
			'maxlength' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('maxlength', $field->errors());
	}

	public function testMinLength(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'minlength' => 5
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('minlength', $field->errors());
	}

	public function testUploads(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'template' => 'test'
			]
		]);

		$this->assertSame(['template' => 'test', 'accept' => '*'], $field->uploads());
	}

	public function testUploadsDisabled(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => false,
		]);

		$this->assertFalse($field->uploads());
	}

	public function testUploadsParent(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'parent' => 'page.parent'
			]
		]);

		$this->assertSame(['parent' => 'page.parent', 'accept' => '*'], $field->uploads());
	}

	public function testUploadsTemplate(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => 'test'
		]);

		$this->assertSame(['template' => 'test', 'accept' => '*'], $field->uploads());
	}

	public function testUploadsWithInvalidInput(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => 1,
		]);

		$this->assertSame(['accept' => '*'], $field->uploads());
	}

	public function testValueTrimmed(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test '
		]);

		$this->assertSame('test', $field->value());
	}
}
