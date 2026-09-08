<?php

namespace Kirby\Form\Fields;

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

		$this->app->impersonate('kirby');
	}

	public function model()
	{
		return $this->app->page('a');
	}

	public function testDefaultProps()
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

	public function testValue()
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

	public function testValueWithProtectedPage(): void
	{
		// the permission cache is keyed by template and role,
		// so both need to be unique for this test
		$uuid = uuid();

		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'blueprints' => [
				'pages/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a'
					],
					[
						'slug'     => 'b',
						'template' => 'secret-' . $uuid
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			],
			'user' => 'editor@getkirby.com'
		]);

		$field = $this->field('pages', [
			'model' => $app->site(),
			'store' => 'id',
			'value' => ['a', 'b']
		]);

		$value = $field->value();

		// the listable page is resolved as usual
		$this->assertSame('a', $value[0]['id']);
		$this->assertSame('a', $value[0]['text']);

		// the page that must not be listed only echoes back
		// the ID that is already stored in the content file
		$this->assertSame(['id', 'uuid', 'image', 'info', 'link', 'permissions', 'sortable', 'text'], array_keys($value[1]));
		$this->assertSame('b', $value[1]['id']);
		$this->assertSame('–', $value[1]['text']);
		$this->assertFalse($value[1]['link']);

		// ... and is still kept when the value is stored again
		$this->assertSame(['a', 'b'], $field->data());
	}

	public function testMin()
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

	public function testMax()
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

	public function testEmpty()
	{
		$field = $this->field('pages', [
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslatedEmpty()
	{
		$field = $this->field('pages', [
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testRequiredProps()
	{
		$field = $this->field('pages', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid()
	{
		$field = $this->field('pages', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid()
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

	public function testApi()
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
