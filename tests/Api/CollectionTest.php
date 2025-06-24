<?php

namespace Kirby\Api;

use Exception;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\TestCase;

class CollectionTest extends TestCase
{
	public function testConstruct(): void
	{
		$api = new Api([]);
		$collection = new Collection($api, [], []);

		$this->assertInstanceOf(Collection::class, $collection);
	}

	public function testSelect(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'fields' => [
						'key'   => fn ($model) => strtolower($model),
						'value' => fn ($model) => $model
					]
				]
			]
		]);

		$collection = new Collection($api, [
			'foo' => 'A',
			'bar' => 'B',
			'baz' => 'C',
		], [
			'model' => 'test'
		]);

		// success
		$result = $collection->select('key')->toArray();

		$this->assertCount(3, $result);
		$this->assertSame(['key' => 'a'], $result[0]);
		$this->assertSame(['key' => 'b'], $result[1]);
		$this->assertSame(['key' => 'c'], $result[2]);

		// invalid select
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid select keys');
		$collection->select(0)->toArray();
	}

	public function testToArray(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'fields' => [
						'value' => fn ($model) => $model
					]
				]
			]
		]);
		$collection = new Collection($api, [
			'foo' => 'A',
			'bar' => 'B',
			'baz' => 'C',
		], [
			'model' => 'test'
		]);

		$result = $collection->toArray();

		$this->assertCount(3, $result);
		$this->assertSame(['value' => 'A'], $result[0]);
		$this->assertSame(['value' => 'B'], $result[1]);
		$this->assertSame(['value' => 'C'], $result[2]);
	}

	public function testToResponse(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			]
		]);
		$collection = new Collection($api, new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b']),
			new Page(['slug' => 'c']),
		]), [
			'model' => 'test'
		]);

		$result = $collection->toResponse();

		$this->assertSame(200, $result['code']);
		$this->assertSame('ok', $result['status']);
		$this->assertSame('collection', $result['type']);
		$this->assertSame([
			['value' => 'a'],
			['value' => 'b'],
			['value' => 'c']
		], $result['data']);
		$this->assertSame([
			'page'   => 1,
			'total'  => 3,
			'offset' => 0,
			'limit'  => 100
		], $result['pagination']);
	}
}
