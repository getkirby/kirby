<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;

class PagesFieldTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Form.Fields.PagesField';

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

	public function testDefaultProps(): void
	{
		$field = $this->field('pages', [
			'model' => $this->model()
		]);

		$this->assertSame('pages', $field->type());
		$this->assertSame('pages', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertNull($field->max());
		$this->assertTrue($field->multiple());
		$this->assertTrue($field->save());
	}

	public function testValue(): void
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

		$this->assertSame($expected, $field->value());
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
		$this->assertTrue($field->required());
		$this->assertArrayHasKey('min', $field->errors());
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

	public function testEmpty(): void
	{
		$field = $this->field('pages', [
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$field = $this->field('pages', [
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('pages', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('pages', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('pages', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true,
			'value' => [
				'a/aa',
			],
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
		$this->assertSame('page://my-test-uuid', $api[0]['id']);
		$this->assertSame('page://my-a', $api[1]['id']);
		$this->assertSame('page://my-b', $api[2]['id']);
	}

	public function testToModel(): void
	{
		$field = $this->field('pages', [
			'model' => new Page(['slug' => 'test']),
		]);

		$model = $field->toModel('a/aa');
		$this->assertInstanceOf(Page::class, $model);
		$this->assertSame('a/aa', $model->id());
	}
}
