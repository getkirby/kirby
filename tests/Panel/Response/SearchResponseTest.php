<?php

namespace Kirby\Panel\Response;

use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SearchResponse::class)]
class SearchResponseTest extends TestCase
{
	public function testFrom(): void
	{
		$response = SearchResponse::from($results = [
			[
				'text' => 'a'
			],
			[
				'text' => 'b'
			],
			[
				'text' => 'c'
			]
		]);

		$pagination = [
			'page'  => 1,
			'limit' => 3,
			'total' => 3
		];

		$this->assertSame($results, $response->data()['results']);
		$this->assertSame($pagination, $response->data()['pagination']);
	}

	public function testKey(): void
	{
		$response = new SearchResponse();
		$this->assertSame('search', $response->key());
	}
}
