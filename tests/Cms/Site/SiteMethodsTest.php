<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteMethodsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteMethods';

	public function testSiteMethod(): void
	{
		new App([
			'siteMethods' => [
				'test' => fn () => 'site method'
			]
		]);

		$site = new Site();
		$this->assertSame('site method', $site->test());
	}
}
