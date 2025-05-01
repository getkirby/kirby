<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class SiteUrlTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteUrl';

	public function testUrlInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$site = new Site();

		$this->assertSame('/en', $site->url());
		$this->assertSame('/de', $site->url('de'));

		// non-existing language
		$this->assertSame('/', $site->url('fr'));
	}

	public function testUrlInSingleLanguageMode(): void
	{
		$site = new Site([
			'url' => $url = 'https://getkirby.com'
		]);

		$this->assertSame($url, $site->url());
		$this->assertSame($url, $site->__toString());
	}
}
