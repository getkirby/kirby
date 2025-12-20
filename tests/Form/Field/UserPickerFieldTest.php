<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Panel\Controller\Dialog\UserPickerDialogController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserPickerField::class)]
#[CoversClass(ModelPickerField::class)]
class UserPickerFieldTest extends TestCase
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
		$this->assertSame('user://leonardo', $api[0]['uuid']);
		$this->assertSame('user://raphael', $api[1]['uuid']);
	}

	public function testDefaultUser(): void
	{
		$this->app->impersonate('raphael@getkirby.com');

		$field = $this->field('users');
		$this->assertSame([], $field->default());

		$field = $this->field('users', ['default' => true]);
		$this->assertSame('raphael', $field->default()[0]);

		$field = $this->field('users', [
			'default' => [
				'raphael@getkirby.com',
				'donatello@getkirby.com'
			]
		]);

		$this->assertSame('raphael@getkirby.com', $field->default()[0]);
		$this->assertSame('donatello@getkirby.com', $field->default()[1]);

		$field = $this->field('users', ['default' => false]);
		$this->assertSame([], $field->default());
	}

	public function testDialogs(): void
	{
		$field   = $this->field('users');
		$dialogs = $field->dialogs();
		$dialog  = $dialogs['picker']();
		$this->assertInstanceOf(UserPickerDialogController::class, $dialog);
	}

	public function testEmpty(): void
	{
		$field = $this->field('users', ['empty' => 'Test']);
		$this->assertSame('Test', $field->empty());

		$field = $this->field('users', [
			'empty' => ['en' => 'Test', 'de' => 'Töst']
		]);
		$this->assertSame('Test', $field->empty());
	}

	public function testIsValid(): void
	{
		$field = $this->field('users', ['required' => true]);
		$this->assertFalse($field->isValid());

		$field = $this->field('tags', [
			'required' => true,
			'value'    => ['leonardo@getkirby.com'],
		]);
		$this->assertTrue($field->isValid());
	}

	public function testMax(): void
	{
		$field = $this->field('users', [
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

	public function testMin(): void
	{
		$field = $this->field('users', [
			'value' => [
				'leonardo@getkirby.com',
				'raphael@getkirby.com'
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
		$field = $this->field('users');
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
			'label'       => 'Users',
			'layout'      => 'list',
			'link'        => true,
			'max'         => null,
			'min'         => null,
			'multiple'    => true,
			'name'        => 'users',
			'query'       => null,
			'required'    => false,
			'saveable'    => true,
			'search'      => true,
			'size'        => 'auto',
			'store'       => 'uuid',
			'text'        => null,
			'translate'   => true,
			'type'        => 'users',
			'when'        => null,
			'width'       => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testRequired(): void
	{
		$field = $this->field('users', ['required' => true]);
		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testToModel(): void
	{
		$field = $this->field('users');
		$model = $field->toModel('leonardo@getkirby.com');
		$this->assertInstanceOf(User::class, $model);
		$this->assertSame('leonardo@getkirby.com', $model->email());
	}

	public function testToStoredValue(): void
	{
		$field = $this->field('users', [
			'value' => [
				'leonardo@getkirby.com', // exists
				'raphael@getkirby.com', // exists
				'homer@getkirby.com'  // does not exist
			]
		]);

		$ids = $field->toStoredValue();

		$expected = [
			'user://leonardo',
			'user://raphael'
		];

		$this->assertSame($expected, $ids);
	}
}
