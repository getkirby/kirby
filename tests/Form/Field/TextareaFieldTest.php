<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Controller\Dialog\FilePickerDialogController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TextareaField::class)]
class TextareaFieldTest extends TestCase
{
	public function testApi(): void
	{
		$field = $this->field('textarea');
		$this->assertSame('upload', $field->api()[0]['pattern']);
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

	public function testDefaultWithStringTemplate(): void
	{
		$field = $this->field('textarea', [
			'default' => ' test {{ 1 + 2 }} ',
		]);

		$this->assertSame('test 3', $field->default());
	}

	public function testDialogs(): void
	{
		$field = $this->field('textarea');

		$dialogs = $field->dialogs();
		$dialog  = $dialogs['files']();
		$this->assertInstanceOf(FilePickerDialogController::class, $dialog);
	}

	public function testAcceptedFileBlueprints(): void
	{
		$field = $this->field('textarea', [
			'uploads' => 'a'
		]);

		$this->assertSame(['a'], $field->acceptedFileBlueprints());
	}

	public function testAcceptedFileBlueprintsWithoutBlueprint(): void
	{
		$field = $this->field('textarea');
		$this->assertSame(['default'], $field->acceptedFileBlueprints());
	}

	public function testAcceptedFileBlueprintsWithOtherParent(): void
	{
		$field = $this->field('textarea', [
			'uploads' => [
				'parent'    => 'site',
				'blueprint' => 'a'
			]
		]);

		$this->assertSame([], $field->acceptedFileBlueprints());
	}

	public function testAcceptedFileBlueprintsWithOtherPage(): void
	{
		$field = $this->field('textarea', [
			'model'   => new Page([
				'slug'   => 'child',
				'parent' => new Page(['slug' => 'parent'])
			]),
			'uploads' => [
				'parent'    => 'page.parent',
				'blueprint' => 'a'
			]
		]);

		$this->assertSame([], $field->acceptedFileBlueprints());
	}

	public function testAcceptedFileBlueprintsWithOwnParent(): void
	{
		$field = $this->field('textarea', [
			'uploads' => [
				'parent'    => 'page',
				'blueprint' => 'a'
			]
		]);

		$this->assertSame(['a'], $field->acceptedFileBlueprints());
	}

	public function testAcceptedFileBlueprintsWithUnresolvableParent(): void
	{
		// the page has no parent
		$field = $this->field('textarea', [
			'uploads' => [
				'parent'    => 'page.parent',
				'blueprint' => 'a'
			]
		]);

		$this->assertSame([], $field->acceptedFileBlueprints());

		// a page id is no valid query
		$field = $this->field('textarea', [
			'uploads' => [
				'parent' => 'test'
			]
		]);

		$this->assertSame([], $field->acceptedFileBlueprints());
	}

	public function testAcceptedFileBlueprintsWithoutUploads(): void
	{
		$field = $this->field('textarea', [
			'uploads' => false
		]);

		$this->assertSame([], $field->acceptedFileBlueprints());
	}

	public function testFont(): void
	{
		$field = $this->field('textarea');
		$this->assertSame('sans-serif', $field->font());

		$field = $this->field('textarea', ['font' => 'monospace']);
		$this->assertSame('monospace', $field->font());
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

	public function testPlaceholder(): void
	{
		$field = $this->field('textarea', [
			'placeholder' => 'Write something'
		]);

		$this->assertSame('Write something', $field->placeholder());
		$this->assertSame('Write something', $field->props()['placeholder']);

		$field = $this->field('textarea', [
			'model'       => new Page([
				'slug'    => 'test',
				'content' => ['title' => 'Test title']
			]),
			'placeholder' => 'About {{ page.title }}'
		]);

		$this->assertSame('About Test title', $field->props()['placeholder']);
	}

	public function testProps(): void
	{
		$field = $this->field('textarea');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'buttons'     => true,
			'counter'     => true,
			'disabled'    => false,
			'font'        => 'sans-serif',
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Textarea',
			'maxlength'   => null,
			'minlength'   => null,
			'name'        => 'textarea',
			'placeholder' => null,
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

	public function testReset(): void
	{
		$field = $this->field('textarea');
		$field->fill('test');
		$this->assertSame('test', $field->toFormValue());

		$field->reset();
		$this->assertSame('', $field->toFormValue());
	}

	public function testToFormValueTrimmed(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test '
		]);

		$this->assertSame('test', $field->toFormValue());
	}

	public function testUploads(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'blueprint' => 'test'
			]
		]);

		$expected = [
			'accept'    => '*',
			'blueprint' => 'test',
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

	public function testUploadsParentWithInvalidQuery(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => [
				'parent'    => 'site.title',
				'blueprint' => 'test'
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"site.title" could not be resolved as a valid parent for the upload');

		$field->uploads();
	}

	public function testUploadsBlueprint(): void
	{
		$field = $this->field('textarea', [
			'value' => 'test',
			'uploads' => 'test'
		]);

		$expected = [
			'accept'    => '*',
			'blueprint' => 'test',
		];

		$this->assertSame($expected, $field->uploads());
	}

	public function testUploadsBlueprintWithAlias(): void
	{
		$field = $this->field('textarea', [
			'uploads' => ['template' => 'test']
		]);

		$this->assertSame('test', $field->uploads()['blueprint']);
		$this->assertArrayNotHasKey('template', $field->uploads());

		// the option's own name wins
		$field = $this->field('textarea', [
			'uploads' => ['blueprint' => 'a', 'template' => 'b']
		]);

		$this->assertSame('a', $field->uploads()['blueprint']);
	}

	public function testUploadsWithInvalidInput(): void
	{
		$field = $this->field('textarea', [
			'value'   => 'test',
			'uploads' => 1,
		]);

		$this->assertSame(['accept' => '*'], $field->uploads());
	}
}
