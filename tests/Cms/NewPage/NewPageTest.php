<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Content\MemoryStorage;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewPage::class)]
class NewPageTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTest';

	public function testMoveToStorage(): void
	{
		$pageA = new Page([
			'slug' => 'test',
		]);

		$contentAOnDisk = null;

		$pageB = $pageA->update($contentB = [
			'title'    => 'Title 1',
			'subtitle' => 'Subtitle 1'
		]);

		$contentBOnDisk = Data::read(static::TMP . '/content/test/default.txt');

		$this->assertSame($contentAOnDisk, $pageA->version()->read());
		$this->assertSame($contentBOnDisk, $pageB->version()->read());

		$pageB->moveToStorage(new MemoryStorage($pageB));

		$this->assertSame($contentBOnDisk, $pageB->version()->read());
		$this->assertInstanceOf(MemoryStorage::class, $pageB->storage());
		$this->assertInstanceOf(MemoryStorage::class, $pageB->version()->model()->storage());

		$pageC = $pageB->update($contentC = [
			'title'    => 'Title 2',
			'subtitle' => 'Subtitle 2'
		]);

		$this->assertSame($contentB, $pageB->content()->toArray());
		$this->assertSame($contentC, $pageC->content()->toArray());

		$contentCOnDisk = Data::read(static::TMP . '/content/test/default.txt');

		$this->assertSame($contentBOnDisk, $contentCOnDisk);
	}
}
