<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Controller\Dialog\FilePickerDialogController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePickerField::class)]
#[CoversClass(ModelPickerField::class)]
class FilePickerFieldTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Fields.FilePickerField';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => ['uuid'  => 'test-a'],
							],
							[
								'filename' => 'b.jpg',
								'content' => ['uuid'  => 'test-b'],
							],
							[
								'filename' => 'c.jpg',
								'content' => ['uuid'  => 'test-c'],
							],
						]
					]
				],
				'drafts' => [
					[
						'slug'  => 'test-draft',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => ['uuid'  => 'draft-a'],
							],
							[
								'filename' => 'b.jpg',
								'content' => ['uuid'  => 'draft-b'],
							],
							[
								'filename' => 'c.jpg',
								'content' => ['uuid'  => 'draft-c'],
							],
						]
					]
				]
			]
		]);
	}

	public function model()
	{
		return $this->app->page('test');
	}

	public function testDefaultProps(): void
	{
		$field = $this->field('files', [
			'model' => $this->model()
		]);
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'empty'       => null,
			'help'        => null,
			'hidden'      => false,
			'image'       => null,
			'info'        => null,
			'label'       => 'Files',
			'layout'      => 'list',
			'link'        => true,
			'max'         => null,
			'min'         => null,
			'multiple'    => true,
			'name'        => 'files',
			'parent'      => 'pages/test',
			'query'       => null,
			'required'    => false,
			'saveable'    => true,
			'search'      => true,
			'size'        => 'auto',
			'store'       => 'uuid',
			'text'        => null,
			'translate'   => true,
			'type'        => 'files',
			'uploads'     => ['accept' => '*'],
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testDialogs(): void
	{
		$field = $this->field('files', [
			'model' => $this->model()
		]);

		$dialogs = $field->dialogs();
		$dialog  = $dialogs['picker']();
		$this->assertInstanceOf(FilePickerDialogController::class, $dialog);
	}

	public function testValue(): void
	{
		$field = $this->field('files', [
			'model' => $this->model(),
			'value' => [
				'a.jpg', // exists
				'b.jpg', // exists
				'e.jpg'  // does not exist
			]
		]);

		$expected = [
			'file://test-a',
			'file://test-b'
		];

		$this->assertSame($expected, $field->value());
	}

	public function testMin(): void
	{
		$field = $this->field('files', [
			'model' => $this->model(),
			'value' => [
				'a.jpg', // exists
				'b.jpg', // exists
			],
			'min' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(3, $field->min());
		$this->assertTrue($field->isRequired());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax(): void
	{
		$field = $this->field('files', [
			'model' => $this->model(),
			'value' => [
				'a.jpg', // exists
				'b.jpg', // exists
			],
			'max' => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testFilesInDraft(): void
	{
		$field = $this->field('files', [
			'model' => $this->app->page('test-draft'),
			'value' => [
				'a.jpg', // exists
				'b.jpg', // exists
				'e.jpg', // does not exist
			]
		]);

		$expected = [
			'file://draft-a',
			'file://draft-b'
		];

		$this->assertSame($expected, $field->value());
	}

	public function testEmpty(): void
	{
		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('files', [
			'model'    => $this->model(),
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('files', [
			'model'    => $this->model(),
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('files', [
			'model'    => $this->model(),
			'required' => true,
			'value'    => ['a.jpg'],
		]);

		$this->assertTrue($field->isValid());
	}

	public function testApiItems(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => ['api.allowImpersonation' => true],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => ['uuid'  => 'my-a'],
							],
							[
								'filename' => 'b.jpg',
								'content' => ['uuid'  => 'my-b'],
							],
							[
								'filename' => 'c.jpg',
								'content' => ['uuid'  => 'my-c'],
							],
						],
						'blueprint' => [
							'title' => 'Test',
							'name' => 'test',
							'fields' => [
								'gallery' => [
									'type' => 'files',
								]
							]
						]
					]
				]
			],
			'request' => [
				'query' => [
					'items' => 'test/a.jpg,test/b.jpg'
				]
			]
		]);

		$app->impersonate('kirby');
		$api = $app->api()->call('pages/test/fields/gallery/items');

		$this->assertCount(2, $api);
		$this->assertSame('file://my-a', $api[0]['uuid']);
		$this->assertSame('file://my-b', $api[1]['uuid']);
	}

	public function testParentModel(): void
	{
		$field = $this->field('files', [
			'model' => $this->model()
		]);

		$this->assertSame($this->model(), $field->parentModel());

		$field = $this->field('files', [
			'model'  => $this->model(),
			'parent' => 'site'
		]);

		$this->assertSame($this->app->site(), $field->parentModel());
	}

	public function testStore(): void
	{
		// Default
		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('uuid', $field->store());

		// Custom
		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
			'store' => 'id'
		]);

		$this->assertSame('id', $field->store());

		// Disabled UUIDs
		$this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('id', $field->store());
	}

	public function testToModel(): void
	{
		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
		]);

		$model = $field->toModel('test/a.jpg');
		$this->assertInstanceOf(File::class, $model);
		$this->assertSame('test/a.jpg', $model->id());
	}
}
