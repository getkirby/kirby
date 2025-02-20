<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPagePurgeTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPagePurge';

	public function testPurge(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$page->children();
		$page->drafts();
		$page->childrenAndDrafts();

		$this->assertNotNull($page->children);
		$this->assertNotNull($page->drafts);
		$this->assertNotNull($page->childrenAndDrafts);

		$this->assertIsPage($page, $page->purge());

		$this->assertNull($page->children);
		$this->assertNull($page->drafts);
		$this->assertNull($page->childrenAndDrafts);
	}
}
