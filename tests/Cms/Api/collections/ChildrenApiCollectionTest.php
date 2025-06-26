<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;

class ChildrenApiCollectionTest extends ApiCollectionTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.ChildrenApiCollection';

	public function testCollection(): void
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
		$this->assertSame('a', $result[0]['id']);
		$this->assertSame('b', $result[1]['id']);
	}
}
