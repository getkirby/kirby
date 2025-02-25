<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Page::class)]
class PageUpdateTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageUpdate';

	public function assertCorrectlyUpdatedPage(
		Page $modified,
		Page $original,
		Pages $drafts,
		Pages $childrenAndDrafts
	) {
		$this->assertSame('Test', $modified->headline()->value());

		// assert that the page status didn't change with the update
		$this->assertSame($original->status(), $modified->status());

		$this->assertSame($modified, $drafts->find('test'));
		$this->assertSame($modified, $childrenAndDrafts->find('test'));
	}

	public static function languageProvider(): array
	{
		return [
			[null],
			['en'],
			['de']
		];
	}

	#[DataProvider('languageProvider')]
	public function testUpdateInMultiLanguageMode($languageCode): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		if ($languageCode !== null) {
			$this->app->setCurrentLanguage($languageCode);
		}

		$page = Page::create([
			'slug' => 'test'
		]);

		$this->assertNull($page->headline()->value());

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();
		$modified          = $page->update(['headline' => 'Test'], $languageCode);

		$this->assertCorrectlyUpdatedPage($modified, $page, $drafts, $childrenAndDrafts);
	}

	public function testUpdateInMultiLanguageModeWithMergedContent(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test'
		]);

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		// add some content in both languages
		$page = $page->update([
			'a' => 'A (en)',
			'b' => 'B (en)'
		], 'en');

		$page = $page->update([
			'a' => 'A (de)',
			'b' => 'B (de)'
		], 'de');

		$this->assertSame('A (en)', $page->content('en')->a()->value());
		$this->assertSame('B (en)', $page->content('en')->b()->value());
		$this->assertSame('A (de)', $page->content('de')->a()->value());
		$this->assertSame('B (de)', $page->content('de')->b()->value());

		$this->assertIsPage($page, $drafts->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));

		// update a single field in the primary language
		$page = $page->update([
			'b' => 'B modified (en)'
		], 'en');

		$this->assertSame('A (en)', $page->content('en')->a()->value());
		$this->assertSame('B modified (en)', $page->content('en')->b()->value());

		$this->assertIsPage($page, $drafts->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));

		// update a single field in the secondary language
		$page = $page->update([
			'b' => 'B modified (de)'
		], 'de');

		$this->assertSame('A (de)', $page->content('de')->a()->value());
		$this->assertSame('B modified (de)', $page->content('de')->b()->value());

		$this->assertIsPage($page, $drafts->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));
	}

	public function testUpdateInSingleLanguageMode(): void
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$this->assertNull($page->headline()->value());

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();
		$modified          = $page->update(['headline' => 'Test']);

		$this->assertCorrectlyUpdatedPage($modified, $page, $drafts, $childrenAndDrafts);
	}

	public function testUpdateHooks(): void
	{
		$phpunit = $this;
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.update:before' => function (Page $page, $values, $strings) use (&$calls, $phpunit) {
					$calls++;
					// the value is not updated yet
					$phpunit->assertSame('foo', $page->category()->value());

					// parent collections are not updated yet
					$phpunit->assertSame('foo', $page->siblings()->pluck('category')[0]->toString());
					$phpunit->assertSame('bar', $page->siblings()->pluck('category')[1]->toString());
					$phpunit->assertSame('foo', $page->parent()->children()->pluck('category')[0]->toString());
					$phpunit->assertSame('bar', $page->parent()->children()->pluck('category')[1]->toString());
				},
				'page.update:after' => function (Page $newPage, Page $oldPage) use (&$calls, $phpunit) {
					$calls++;
					// the value should now be updated
					$phpunit->assertSame('homer', $newPage->category()->value());

					// check if parent collections are updated
					$phpunit->assertSame('homer', $newPage->siblings()->pluck('category')[0]->toString());
					$phpunit->assertSame('bar', $newPage->siblings()->pluck('category')[1]->toString());
					$phpunit->assertSame('homer', $newPage->parent()->children()->pluck('category')[0]->toString());
					$phpunit->assertSame('bar', $newPage->parent()->children()->pluck('category')[1]->toString());
				}
			]
		]);

		$this->app->impersonate('kirby');

		$parent = Page::create([
			'slug'  => 'test',
			'draft' => false,
		]);

		$a = $parent->createChild([
			'slug'  => 'a',
			'draft' => false,
			'content' => [
				'category' => 'foo'
			]
		]);

		$b = $parent->createChild([
			'slug'  => 'b',
			'draft' => false,
			'content' => [
				'category' => 'bar'
			]
		]);

		$a->update(['category' => 'homer']);

		$this->assertSame(2, $calls);
	}

	public function testUpdateWithDateBasedNumbering(): void
	{
		$page = Page::create([
			'slug' => 'test',
			'blueprint' => [
				'title' => 'Test',
				'name'  => 'test',
				'num'   => 'date'
			],
			'content' => [
				'date' => '2012-12-12'
			]
		]);

		// publish the new page
		$page = $page->changeStatus('listed');

		$this->assertSame(20121212, $page->num());

		$modified = $page->update([
			'date' => '2016-11-21'
		]);

		$this->assertSame(20161121, $modified->num());
	}

}
