<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;

class PagesApiCollectionTest extends ApiCollectionTestCase
{
	public function testCollection()
	{
		$collection = $this->api->collection('pages', new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b'])
		]));

		$result = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertEquals('a', $result[0]['id']);
		$this->assertEquals('b', $result[1]['id']);
	}
}
