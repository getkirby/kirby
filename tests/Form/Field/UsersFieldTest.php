<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\User;

class UsersFieldTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'id'    => 'leonardo',
					'email' => 'leonardo@getkirby.com'
				],
				[
					'id'    => 'raphael',
					'email' => 'raphael@getkirby.com'
				],
				[
					'id'    => 'michelangelo',
					'email' => 'michelangelo@getkirby.com'
				],
				[
					'id'    => 'donatello',
					'email' => 'donatello@getkirby.com'
				]
			]
		]);
	}

	public function testDefaultProps(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test'])
		]);

		$this->assertSame('users', $field->type());
		$this->assertSame('users', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertNull($field->max());
		$this->assertTrue($field->multiple());
		$this->assertTrue($field->save());
	}

	public function testDefaultUser(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test'])
		]);

		$this->assertSame([], $field->default());
	}

	public function testCurrentDefaultUser(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users', [
			'model'   => new Page(['slug' => 'test']),
			'default' => true
		]);

		$this->assertSame('raphael', $field->default()[0]);
	}

	public function testMultipleDefaultUsers(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users', [
			'model'   => new Page(['slug' => 'test']),
			'default' => [
				'raphael@getkirby.com',
				'donatello@getkirby.com'
			]
		]);

		$this->assertSame('raphael', $field->default()[0]);
		$this->assertSame('donatello', $field->default()[1]);
	}

	public function testDefaultUserDisabled(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users', [
			'model'   => new Page(['slug' => 'test']),
			'default' => false
		]);

		$this->assertSame([], $field->default());
	}

	public function testGetIdFromArray(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('user://leonardo', $field->getIdFromArray([
			'email' => 'leonardo@getkirby.com',
			'id'    => 'leonardo',
			'uuid'  => 'user://leonardo'
		]));

		$this->assertSame('leonardo', $field->getIdFromArray([
			'email' => 'leonardo@getkirby.com',
			'id'    => 'leonardo'
		]));

		$this->assertSame('leonardo@getkirby.com', $field->getIdFromArray([
			'email' => 'leonardo@getkirby.com'
		]));

		$this->assertNull($field->getIdFromArray([]));
	}

	public function testMin(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'value' => [
				'leonardo@getkirby.com',
				'raphael@getkirby.com'
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
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'value' => [
				'leonardo@getkirby.com',
				'raphael@getkirby.com'
			],
			'max' => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testEmpty(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'empty' => 'Test'
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testTranslatedEmpty(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);

		$this->assertSame('Test', $field->empty());
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('users', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('users', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('tags', [
			'model'    => new Page(['slug' => 'test']),
			'required' => true,
			'value' => [
				'leonardo@getkirby.com',
			],
		]);

		$this->assertTrue($field->isValid());
	}

	public function testApiItems(): void
	{
		$app = $this->app->clone([
			'options' => ['api.allowImpersonation' => true],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'blueprint' => [
							'title' => 'Test',
							'name' => 'test',
							'fields' => [
								'authors' => [
									'type' => 'users',
								]
							]
						]
					]
				]
			],
			'request' => [
				'query' => [
					'items' => 'leonardo@getkirby.com,raphael@getkirby.com'
				]
			]
		]);

		$app->impersonate('kirby');
		$api = $app->api()->call('pages/test/fields/authors/items');

		$this->assertCount(2, $api);
		$this->assertSame('leonardo', $api[0]['id']);
		$this->assertSame('raphael', $api[1]['id']);
	}

	public function testToModel(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
		]);

		$model = $field->toModel('leonardo@getkirby.com');
		$this->assertInstanceOf(User::class, $model);
		$this->assertSame('leonardo@getkirby.com', $model->email());
	}

	public function testValue(): void
	{
		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'value' => [
				'leonardo@getkirby.com', // exists
				'raphael@getkirby.com', // exists
				'homer@getkirby.com'  // does not exist
			]
		]);

		$expected = [
			'leonardo',
			'raphael'
		];

		$this->assertSame($expected, $field->value());
	}
}
