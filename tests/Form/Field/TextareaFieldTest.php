<?php

namespace Kirby\Form\Field;

use Kirby\Panel\Controller\Dialog\FilePickerDialogController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TextareaField::class)]
class TextareaFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('textarea');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'buttons'     => true,
			'counter'     => true,
			'default'     => null,
			'disabled'    => false,
			'font'        => 'sans-serif',
			'help'        => null,
			'hidden'      => false,
			'label'       => 'Textarea',
			'maxlength'   => null,
			'minlength'   => null,
			'name'        => 'textarea',
			'required'    => false,
			'saveable'    => true,
			'size'        => null,
			'spellcheck'  => null,
			'translate'   => true,
			'type'        => 'textarea',
			'uploads'     => ['accept' => '*'],
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
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

	public function testDialogs(): void
	{
		$field = $this->field('textarea');

		$dialogs = $field->dialogs();
		$dialog  = $dialogs['files']();
		$this->assertInstanceOf(FilePickerDialogController::class, $dialog);
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

	public function testReset(): void
	{
		$field = $this->field('textarea');
		$field->fill('test');

		$this->assertSame('test', $field->toFormValue());

		$field->reset();

		$this->assertSame('', $field->toFormValue());
	}

	public function testUploads(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'template' => 'test'
			]
		]);

		$expected = [
			'accept'   => '*',
			'template' => 'test',
		];

		$this->assertSame($expected, $field->uploads());
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

		$expected = [
			'accept' => '*',
			'parent' => 'page.parent',
		];

		$this->assertSame($expected, $field->uploads());
	}

	public function testUploadsTemplate(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => 'test'
		]);

		$expected = [
			'accept'   => '*',
			'template' => 'test',
		];

		$this->assertSame($expected, $field->uploads());
	}

	public function testUploadsWithInvalidInput(): void
	{
		$field = $this->field('textarea', [
			'value'   => 'test',
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
