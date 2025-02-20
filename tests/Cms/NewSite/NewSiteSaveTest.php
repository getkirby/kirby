<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class NewSiteSaveTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteSave';

	public function testSave(): void
	{
		$site = new Site();
		$site = $site->clone(['content' => ['copyright' => 2012]])->save();
		$this->assertSame(2012, $site->copyright()->value());
	}
}
