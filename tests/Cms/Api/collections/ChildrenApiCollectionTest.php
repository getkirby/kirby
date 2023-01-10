<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;

class ChildrenApiCollectionTest extends ApiCollectionTestCase
{
	public function testCollection()
	{
		$site = new Site([
			'children' => [
				['slug' => 'a'],
				['slug' => 'b'],
			]
		]);

		$collection = $this->api->collection('children', $site->children());
		$result     = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertEquals('a', $result[0]['id']);
		$this->assertEquals('b', $result[1]['id']);
	}
}
