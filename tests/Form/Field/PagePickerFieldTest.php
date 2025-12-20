<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Panel\Controller\Dialog\PagePickerDialogController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PagePickerField::class)]
#[CoversClass(ModelPickerField::class)]
class PagePickerFieldTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Fields.PagePickerField';

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
						'slug'     => 'a',
						'content'  => ['uuid'  => 'my-a'],
						'children' => [
							[
								'slug' => 'aa',
								'content' => ['uuid'  => 'my-aa'],
							],
							[
								'slug' => 'ab',
								'content' => ['uuid'  => 'my-ab'],
							],
						]
					],
					[
						'slug' => 'b',
						'content' => ['uuid'  => 'my-b'],
					],
				]
			]
		]);
	}

	public function model()
	{
		return $this->app->page('a');
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
						'content' => [
							'title' => 'Test Title',
							'uuid'  => 'my-test-uuid'
						],
						'blueprint' => [
							'title' => 'Test',
							'name' => 'test',
							'fields' => [
								'related' => [
									'type' => 'pages',
								]
							]
						]
					],
					[
						'slug' => 'a',
						'content' => ['uuid'  => 'my-a'],
					],
					[
						'slug' => 'b',
						'content' => ['uuid'  => 'my-b'],
					],
					[
						'slug' => 'c',
						'content' => ['uuid'  => 'my-c'],
					],
				]
			],
			'request' => [
				'query' => [
					'items' => 'test,a,b'
				]
			]
		]);

		$app->impersonate('kirby');
		$api = $app->api()->call('pages/test/fields/related/items');

		$this->assertCount(3, $api);
		$this->assertSame('page://my-test-uuid', $api[0]['uuid']);
		$this->assertSame('page://my-a', $api[1]['uuid']);
		$this->assertSame('page://my-b', $api[2]['uuid']);
	}

	public function testDialogs(): void
	{
		$field = $this->field('pages', [
			'model' => $this->model()
		]);

		$dialogs = $field->dialogs();
		$dialog  = $dialogs['picker']();
		$this->assertInstanceOf(PagePickerDialogController::class, $dialog);
	}

	public function testEmpty(): void
	{
		$field = $this->field('pages', ['empty' => 'Test']);
		$this->assertSame('Test', $field->empty());

		$field = $this->field('pages', [
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);
		$this->assertSame('Test', $field->empty());
	}

	public function testIsValid(): void
	{
		$field = $this->field('pages', ['required' => true]);
		$this->assertFalse($field->isValid());

		$field = $this->field('pages', [
			'required' => true,
			'value'    => ['a/aa'],
		]);
		$this->assertTrue($field->isValid());
	}

	public function testMax(): void
	{
		$field = $this->field('pages', [
			'model' => $this->model(),
			'value' => [
				'a/aa', // exists
				'a/ab', // exists
			],
			'max' => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testMin(): void
	{
		$field = $this->field('pages', [
			'model' => $this->model(),
			'value' => [
				'a/aa', // exists
				'a/ab', // exists
			],
			'min' => 3
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(3, $field->min());
		$this->assertTrue($field->isRequired());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testProps(): void
	{
		$field = $this->field('pages', [
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
			'label'       => 'Pages',
			'layout'      => 'list',
			'link'        => true,
			'max'         => null,
			'min'         => null,
			'multiple'    => true,
			'name'        => 'pages',
			'query'       => null,
			'required'    => false,
			'saveable'    => true,
			'search'      => true,
			'size'        => 'auto',
			'store'       => 'uuid',
			'subpages'    => true,
			'text'        => null,
			'translate'   => true,
			'type'        => 'pages',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testRequired(): void
	{
		$field = $this->field('pages', ['required' => true]);
		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testToFormValue(): void
	{
		$field = $this->field('pages', [
			'model' => $this->model(),
			'value' => [
				'a/aa', // exists
				'a/ab', // exists
				'a/ac'  // does not exist
			]
		]);

		$expected = [
			'page://my-aa',
			'page://my-ab'
		];

		$this->assertSame($expected, $field->toFormValue());
	}

	public function testToModel(): void
	{
		$field = $this->field('pages');
		$model = $field->toModel('a/aa');
		$this->assertInstanceOf(Page::class, $model);
		$this->assertSame('a/aa', $model->id());
	}
}
