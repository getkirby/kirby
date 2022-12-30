<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;
use Kirby\Filesystem\Dir;

class LanguagesApiCollectionTest extends ApiCollectionTestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$this->api = $this->app->api();
		Dir::make($this->tmp);
	}

	public function testCollection()
	{
		$collection = $this->api->collection('languages', $this->app->languages());
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('en', $result[0]['code']);
		$this->assertSame('de', $result[1]['code']);
	}
}
