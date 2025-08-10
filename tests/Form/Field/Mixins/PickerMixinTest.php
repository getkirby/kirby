<?php

namespace Kirby\Form\Field;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;

class PickerMixinTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Form.Fields.PickerMixin';

	protected Field $field;

	public function setUp(): void
	{
		$kirby = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$kirby->impersonate('kirby');
	}

	public function tearDown(): void
	{
		App::destroy();
	}

	public function testGetIdFromArray(): void
	{
		Field::$types = [
			'test' => [
				'mixins' => ['picker']
			]
		];

		$field = $this->field('test', [
			'model' => new Page(['slug' => 'test']),
		]);

		$this->assertSame('page://aa', $field->getIdFromArray([
			'id'    => 'a/aa',
			'uuid'  => 'page://aa'
		]));

		$this->assertSame('a/aa', $field->getIdFromArray([
			'id'    => 'a/aa'
		]));

		$this->assertNull($field->getIdFromArray([]));
	}

	public function testToItem(): void
	{
		Field::$types = [
			'test' => [
				'mixins' => ['picker']
			]
		];

		$field = $this->field('test', [
			'model' => new Page(['slug' => 'test']),
		]);

		$item = $field->toItem(new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Test Title'
			]
		]));

		$this->assertIsArray($item);
		$this->assertArrayHasKey('image', $item);
		$this->assertSame('test', $item['id']);
		$this->assertSame('Test Title', $item['text']);
	}

	public function testToItems(): void
	{
		Field::$types = [
			'test' => [
				'mixins' => ['picker']
			]
		];

		$field = $this->field('test', [
			'model' => new Page(['slug' => 'test']),
		]);

		$items = $field->toItems([
			new Page(['slug' => 'test']),
			new Page(['slug' => 'test2'])
		]);

		$this->assertIsArray($items);
		$this->assertCount(2, $items);
		$this->assertArrayHasKey('image', $items[0]);
		$this->assertArrayHasKey('id', $items[0]);
		$this->assertArrayHasKey('text', $items[0]);
	}

	public function testToModel(): void
	{
		Field::$types = [
			'test' => [
				'mixins' => ['picker']
			]
		];

		$field = $this->field('test', [
			'model' => new Page(['slug' => 'test']),
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('toModel() is not implemented on test field');
		$field->toModel('a/aa');
	}

	public function testToStoredValues(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					['slug' => 'aa', 'content' => ['uuid' => 'aa']],
					['slug' => 'bb', 'content' => ['uuid' => 'bb']]
				]
			]
		]);

		$app->impersonate('kirby');

		$field = $this->field('pages', [
			'model' => new Page(['slug' => 'test']),
			'store' => 'id'
		]);

		$values = $field->toStoredValues([
			'page://aa',
			'page://bb'
		]);

		$this->assertSame(['aa', 'bb'], $values);
	}
}
