<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Content\MemoryStorage;
use Kirby\Content\PlainTextStorage;

/**
 * @coversDefaultClass \Kirby\Cms\NewPage
 */
class NewPageMoveToStorageTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageMoveToStorageTest';

	public function testMoveToStorage(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PlainTextStorage::class, $page->storage());

		$contentBeforeMove = $page->content()->toArray();

		$page->moveToStorage(new MemoryStorage($page));

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());

		$this->assertSame($contentBeforeMove, $page->content()->toArray());
	}

}
