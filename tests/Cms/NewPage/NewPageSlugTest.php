<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageSlugTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageSlugTest';

	public function testSlugInSingleLanguageMode()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->slug());
	}

	public function testSlugInMultiLanguageMode()
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'de',
					'slug' => 'test-de'
				]
			]
		]);

		$this->assertSame('test', $page->slug());
		$this->assertSame('test', $page->slug('en'));
		$this->assertSame('test-de', $page->slug('de'));
	}

	public function testSlugInMultiLanguageModeWithSlugFieldInDefaultTranslation()
	{
		$this->setUpMultiLanguage();

		$page = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'slug' => 'test-en'
				]
			]
		]);

		// In our current logic, a slug field in the default translation is not respected.
		// We might want to change this in the future.
		$this->assertSame('test', $page->slug());
		$this->assertSame('test', $page->slug('en'));
	}

	public function testSlugWithoutValue()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The page slug is required');

		$page = new Page(['slug' => null]);
	}
}
