<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;

class PagesFieldTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields.PagesField';

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
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'aa',
							],
							[
								'slug' => 'ab',
							]
						]
					],
					[
						'slug' => 'b',
					]
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

		$value = $field->value();
		$ids   = array_column($value, 'id');

		$expected = [
			'a/aa',
			'a/ab'
		];

		$this->assertSame($expected, $ids);
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

	public function testApi(): void
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
					['slug' => 'a'],
					['slug' => 'b'],
					['slug' => 'c'],
				]
			]
		]);

		$app->impersonate('kirby');
		$api = $app->api()->call('pages/test/fields/related');

		$this->assertCount(3, $api);
		$this->assertArrayHasKey('data', $api);
		$this->assertArrayHasKey('pagination', $api);
		$this->assertArrayHasKey('model', $api);
		$this->assertCount(4, $api['data']);
		$this->assertSame('test', $api['data'][0]['id']);
		$this->assertSame([
			'id' => 'test',
			'image' => [
				'back' => 'pattern',
				'color' => 'gray-500',
				'cover' => false,
				'icon' => 'page'
			],
			'info' => '',
			'link' => '/pages/test',
			'sortable' => true,
			'text' => 'Test Title',
			'uuid' => 'page://my-test-uuid',
			'dragText' => '(link: page://my-test-uuid text: Test Title)',
			'hasChildren' => false,
			'url' => '/test',
		], $api['data'][0]);
		$this->assertSame('a', $api['data'][1]['id']);
		$this->assertSame('b', $api['data'][2]['id']);
		$this->assertSame('c', $api['data'][3]['id']);
	}
}
