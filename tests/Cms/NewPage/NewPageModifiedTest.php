<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageModifiedTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageModified';

	public function testModifiedInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		// create the english page
		F::write($file = static::TMP . '/content/test/test.en.txt', 'test');
		touch($file, $modified = \time() + 2);

		$page = $this->app->page('test');

		$this->assertSame($modified, $page->modified());

		// create the german page
		F::write($file = static::TMP . '/content/test/test.de.txt', 'test');
		touch($file, $modified = \time() + 5);

		// change the language
		$this->app->setCurrentLanguage('de');
		$this->app->setCurrentTranslation('de');

		$this->assertSame($modified, $page->modified());
	}

	public function testModifiedInSingleLanguageMode(): void
	{
		// create a page
		F::write($file = static::TMP . '/content/test/test.txt', 'test');

		$modified = filemtime($file);
		$page     = $this->app->page('test');

		$this->assertSame($modified, $page->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $page->modified($format));

		// custom date handler without format
		$this->assertSame($modified, $page->modified(null, 'strftime'));

		// custom date handler with format
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $page->modified($format, 'strftime'));
	}

	public function testModifiedInMultiLanguageModeSpecifyingLanguage(): void
	{
		$this->setUpMultiLanguage();

		// create the english page
		F::write($file = static::TMP . '/content/test/test.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german page
		F::write($file = static::TMP . '/content/test/test.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$page = $this->app->page('test');

		$this->assertSame($modifiedEnContent, $page->modified(null, null, 'en'));
		$this->assertSame($modifiedDeContent, $page->modified(null, null, 'de'));
	}
}
