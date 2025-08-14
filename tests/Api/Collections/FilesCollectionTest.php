<?php

namespace Kirby\Api;

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Page;

class FilesCollectionTest extends CollectionTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Api.FilesCollection';

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
