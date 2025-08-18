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

		Field::$types = [
			'test' => [
				'mixins' => ['picker']
			]
		];

		$kirby->impersonate('kirby');

		$this->field = $this->field('test', [
			'model' => new Page(['slug' => 'test']),
		]);
	}

	public function tearDown(): void
	{
		App::destroy();
	}

	public function testGetIdFromArray(): void
	{
		$this->assertSame('page://aa', $this->field->getIdFromArray([
			'id'    => 'a/aa',
			'uuid'  => 'page://aa'
		]));

		$this->assertSame('a/aa', $this->field->getIdFromArray([
			'id'    => 'a/aa'
		]));

		$this->assertNull($this->field->getIdFromArray([]));
	}

	public function testToItem(): void
	{
		$item = $this->field->toItem(new Page([
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
		$items = $this->field->toItems([
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
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('toModel() is not implemented on test field');
		$this->field->toModel('a/aa');
	}

	public function testToStoredValues(): void
	{
		$values = $this->field->toStoredValues([
			['uuid' => 'page://aa'],
			['uuid' => 'page://bb']
		]);

		$this->assertSame(['page://aa', 'page://bb'], $values);
	}
}
