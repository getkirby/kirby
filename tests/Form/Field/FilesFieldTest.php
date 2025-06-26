<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;

class FilesFieldTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields.Languages';

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
								'filename' => 'a.jpg'
							],
							[
								'filename' => 'b.jpg'
							],
							[
								'filename' => 'c.jpg'
							]
						]
					]
				],
				'drafts' => [
					[
						'slug'  => 'test-draft',
						'files' => [
							[
								'filename' => 'a.jpg'
							],
							[
								'filename' => 'b.jpg'
							],
							[
								'filename' => 'c.jpg'
							]
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

		$this->assertSame('files', $field->type());
		$this->assertSame('files', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertSame('list', $field->layout());
		$this->assertNull($field->max());
		$this->assertTrue($field->multiple());
		$this->assertTrue($field->save());
		$this->assertSame('uuid', $field->store());
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

		$value = $field->value();
		$ids   = array_column($value, 'id');

		$expected = [
			'a.jpg',
			'b.jpg'
		];

		$this->assertSame($expected, $ids);
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
		$this->assertTrue($field->required());
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

		$value = $field->value();
		$ids   = array_column($value, 'id');

		$expected = [
			'a.jpg',
			'b.jpg'
		];

		$this->assertSame($expected, $ids);
	}

	public function testQueryWithPageParent(): void
	{
		$field = $this->field('files', [
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('page.files', $field->query());
	}

	public function testQueryWithSiteParent(): void
	{
		$field = $this->field('files', [
			'model' => new Site(),
		]);

		$this->assertSame('site.files', $field->query());
	}

	public function testQueryWithUserParent(): void
	{
		$field = $this->field('files', [
			'model' => new User(['email' => 'test@getkirby.com']),
		]);

		$this->assertSame('user.files', $field->query());
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
			'value' => [
				'a.jpg',
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
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg'],
							['filename' => 'c.jpg'],
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
			]
		]);

		$app->impersonate('kirby');
		$api = $app->api()->call('pages/test/fields/gallery');

		$this->assertCount(2, $api);
		$this->assertArrayHasKey('data', $api);
		$this->assertArrayHasKey('pagination', $api);
		$this->assertCount(3, $api['data']);
		$this->assertSame('a.jpg', $api['data'][0]['id']);
		$this->assertSame('b.jpg', $api['data'][1]['id']);
		$this->assertSame('c.jpg', $api['data'][2]['id']);
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
}
