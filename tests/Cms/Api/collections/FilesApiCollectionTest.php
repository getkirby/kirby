<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiCollectionTestCase;

class FilesApiCollectionTest extends ApiCollectionTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FilesApiCollection';

	public function testCollection(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$collection = $this->api->collection('files', new Files([
			new File(['filename' => 'a.jpg', 'parent' => $page]),
			new File(['filename' => 'b.jpg', 'parent' => $page])
		]));

		$result = $collection->toArray();

		$this->assertCount(2, $result);
		$this->assertSame('a.jpg', $result[0]['filename']);
		$this->assertSame('b.jpg', $result[1]['filename']);
	}
}
