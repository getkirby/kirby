<?php

namespace Kirby\Api;

use Kirby\Cms\Page;
use Kirby\Cms\Pages;

class PagesCollectionTest extends CollectionTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PagesApiCollection';

	public function testCollection(): void
	{
		$collection = $this->api->collection('pages', new Pages([
			new Page(['slug' => 'a']),
			new Page(['slug' => 'b'])
		]));

		$result = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('a', $result[0]['id']);
		$this->assertSame('b', $result[1]['id']);
	}
}
