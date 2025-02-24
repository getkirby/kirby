<?php

namespace Kirby\Cms;

use Kirby\Content\MemoryStorage;
use Kirby\Content\PlainTextStorage;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChangeStorageTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageChangeStorage';

	public function testChangeStorage(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PlainTextStorage::class, $page->storage());

		$contentBeforeMove = $page->content()->toArray();

		$page->changeStorage(MemoryStorage::class);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());
		$this->assertSame($contentBeforeMove, $page->content()->toArray());
	}

	public function testChangeStorageWithObject(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(PlainTextStorage::class, $page->storage());

		$contentBeforeMove = $page->content()->toArray();

		$storage = new MemoryStorage($page);
		$page->changeStorage($storage);

		$this->assertInstanceOf(MemoryStorage::class, $page->storage());
		$this->assertSame($contentBeforeMove, $page->content()->toArray());
	}
}
