<?php

namespace Kirby\Api;

use Exception;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

	public function testToResponseIgnoresFilterByInQuery(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			],
			'requestData' => [
				'query' => [
					'query' => [
						'filterBy' => [
							['field' => 'slug', 'operator' => '==', 'value' => 'a']
						]
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

		// filterBy is stripped; all three pages are returned
		$this->assertCount(3, $collection->toResponse()['data']);
	}

	public function testToResponseIgnoresSortByInQuery(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			],
			'requestData' => [
				'query' => [
					'query' => [
						'sortBy' => 'slug desc'
					]
				]
			]
		]);
		$collection = new Collection($api, new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b']),
		]), [
			'model' => 'test'
		]);

		// sortBy is stripped; original order (a, b) is preserved
		$result = $collection->toResponse()['data'];
		$this->assertSame('a', $result[0]['value']);
		$this->assertSame('b', $result[1]['value']);
	}

	public function testToResponseRespectsLimitInQuery(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			],
			'requestData' => [
				'query' => [
					'query' => [
						'limit' => 2
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

		$this->assertCount(2, $collection->toResponse()['data']);
	}

	public static function invalidLimitProvider(): array
	{
		return [
			[['2'], 100],
			['abc', 100],
			// empty values are left to the pagination default
			['', 20],
			['0', 20],
		];
	}

	#[DataProvider('invalidLimitProvider')]
	public function testToResponseWithInvalidLimit(mixed $limit, int $expected): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			],
			'requestData' => [
				'query' => [
					'limit' => $limit
				]
			]
		]);
		$collection = new Collection($api, new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b'])
		]), [
			'model' => 'test'
		]);

		// malformed `limit` query falls back to the default
		$response = $collection->toResponse();
		$this->assertCount(2, $response['data']);
		$this->assertSame($expected, $response['pagination']['limit']);
	}

	public static function invalidPageProvider(): array
	{
		return [
			[['2']],
			[''],
			['abc'],
		];
	}

	#[DataProvider('invalidPageProvider')]
	public function testToResponseWithInvalidPage(mixed $page): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			],
			'requestData' => [
				'query' => [
					'limit' => 1,
					'page'  => $page
				]
			]
		]);
		$collection = new Collection($api, new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b'])
		]), [
			'model' => 'test'
		]);

		// malformed `page` query falls back to the default
		$response = $collection->toResponse();
		$this->assertSame([['value' => 'a']], $response['data']);
		$this->assertSame(1, $response['pagination']['page']);
	}

	public function testToResponseWithLimitAndPage(): void
	{
		$api = new Api([
			'models' => [
				'test' => [
					'type'   => Page::class,
					'fields' => [
						'value' => fn ($model) => $model->slug()
					]
				]
			],
			'requestData' => [
				'query' => [
					'limit' => '1',
					'page'  => '2'
				]
			]
		]);
		$collection = new Collection($api, new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b'])
		]), [
			'model' => 'test'
		]);

		$response = $collection->toResponse();
		$this->assertSame([['value' => 'b']], $response['data']);
		$this->assertSame(2, $response['pagination']['page']);
		$this->assertSame(1, $response['pagination']['limit']);
	}
}
