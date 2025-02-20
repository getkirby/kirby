<?php

namespace Kirby\Cms;

use Kirby\Cms\NewSite as Site;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Site::class)]
class NewSiteModifiedTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewSiteModified';

	public function testModifiedInMultilangInstallation(): void
	{
		$this->setUpMultiLanguage();

		// create the english site
		F::write($file = static::TMP . '/content/site.en.txt', 'test');
		touch($file, $modified = \time() + 2);

		$site = new Site();
		$this->assertSame($modified, $site->modified());

		// create the german site
		F::write($file = static::TMP . '/content/site.de.txt', 'test');
		touch($file, $modified = \time() + 5);

		// change the language
		$this->app->setCurrentLanguage('de');
		$this->app->setCurrentTranslation('de');

		$this->assertSame($modified, $site->modified());
	}

	public function testModifiedInSingleLanguageMode(): void
	{
		// create the site file
		F::write($file = static::TMP . '/content/site.txt', 'test');

		$modified = filemtime($file);
		$site     = new Site();

		$this->assertSame($modified, $site->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $site->modified($format));

		// custom date handler
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $site->modified($format, 'strftime'));
	}
}
