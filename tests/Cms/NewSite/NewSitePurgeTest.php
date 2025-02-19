<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class NewSitePurgeTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSitePurgeTest';

	public function testPurge(): void
	{
		$site = new Site();
		$site->children();
		$site->drafts();
		$site->childrenAndDrafts();

		$this->assertNotNull($site->children);
		$this->assertNotNull($site->drafts);
		$this->assertNotNull($site->childrenAndDrafts);

		$site->purge();

		$this->assertNull($site->children);
		$this->assertNull($site->drafts);
		$this->assertNull($site->childrenAndDrafts);
	}
}
