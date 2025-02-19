<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewSite::class)]
class NewSiteMethodsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteMethodsTest';

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
