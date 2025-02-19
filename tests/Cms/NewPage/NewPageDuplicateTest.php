<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageDuplicateTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageDuplicateTest';

	public function testDuplicate()
	{
		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		// check UUID exists
		$oldUuid = $page->content()->get('uuid')->value();
		$this->assertIsString($oldUuid);

		$drafts = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$copy = $page->duplicate('test-copy');

		$this->assertIsPage($page, $drafts->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));

		// check UUID got updated
		$newUuid = $copy->content()->get('uuid')->value();
		$this->assertIsString($newUuid);
		$this->assertNotSame($oldUuid, $newUuid);
	}

}
