<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteSaveTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteSave';

	public function testSave(): void
	{
		$site = new Site();
		$site = $site->clone(['content' => ['copyright' => 2012]])->save();
		$this->assertSame(2012, $site->copyright()->value());
	}
}
