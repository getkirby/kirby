<?php

namespace Kirby\Panel\Response;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Response\SearchResponse
 */
class SearchResponseTest extends TestCase
{
	/**
	 * @covers ::from
	 */
	public function testFrom()
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

	/**
	 * @covers ::key
	 */
	public function testKey()
	{
		$response = new SearchResponse();
		$this->assertSame('search', $response->key());
	}
}
