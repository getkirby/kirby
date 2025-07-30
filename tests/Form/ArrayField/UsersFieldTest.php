<?php

namespace Kirby\Form\ArrayField;

use Kirby\Cms\App;
use Kirby\Cms\Page;

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
					'email' => 'leonardo@getkirby.com'
				],
				[
					'email' => 'raphael@getkirby.com'
				],
				[
					'email' => 'michelangelo@getkirby.com'
				],
				[
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

		$this->assertSame('raphael@getkirby.com', $field->default()[0]['email']);
	}

	public function testMultipleDefaultUsers(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'default' => [
				'raphael@getkirby.com',
				'donatello@getkirby.com'
			]
		]);

		$this->assertSame('raphael@getkirby.com', $field->default()[0]['email']);
		$this->assertSame('donatello@getkirby.com', $field->default()[1]['email']);
	}

	public function testDefaultUserDisabled(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users', [
			'model' => new Page(['slug' => 'test']),
			'default' => false
		]);

		$this->assertSame([], $field->default());
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

		$value = $field->value();
		$ids   = array_column($value, 'email');

		$expected = [
			'leonardo@getkirby.com',
			'raphael@getkirby.com'
		];

		$this->assertSame($expected, $ids);
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

	public function testApi(): void
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
			]
		]);

		$app->impersonate('kirby');
		$api = $app->api()->call('pages/test/fields/authors');

		$this->assertCount(2, $api);
		$this->assertArrayHasKey('data', $api);
		$this->assertArrayHasKey('pagination', $api);
		$this->assertCount(4, $api['data']);
		$this->assertSame('donatello@getkirby.com', $api['data'][0]['email']);
		$this->assertSame('leonardo@getkirby.com', $api['data'][1]['email']);
		$this->assertSame('michelangelo@getkirby.com', $api['data'][2]['email']);
		$this->assertSame('raphael@getkirby.com', $api['data'][3]['email']);
	}
}
