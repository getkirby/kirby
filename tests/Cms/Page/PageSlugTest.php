<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class PageSlugTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageSlug';

	public function testSlugInSingleLanguageMode(): void
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->slug());
	}

	public function testSlugInMultiLanguageMode(): void
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

	public function testSlugInMultiLanguageModeWithSlugFieldInDefaultTranslation(): void
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

	public function testSlugInMultiLanguageModeWhenChangesExist(): void
	{
		$this->setUpMultiLanguage();

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test'
		]);

		$page->version('changes')->save([
			'text' => 'Some additional text'
		], 'de');

		$modified = $page->changeSlug('test-translated', 'de');

		$this->assertSame('test-translated', $modified->slug('de'));
		$this->assertSame('test', $modified->slug());

		$changes = $modified->version('changes')->content('de');

		$this->assertSame('test-translated', $changes->get('slug')->value());
		$this->assertSame('Some additional text', $changes->get('text')->value());
	}

	public function testSlugWithInvalidValue(): void
	{
		$this->expectException(TypeError::class);
		new Page(['slug' => []]);
	}

	public function testSlugWithoutValue(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The page slug is required');

		new Page(['slug' => null]);
	}
}
