<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageUpdateTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageUpdateTest';

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

	/**
	 * @dataProvider languageProvider
	 */
	public function testUpdateInMultiLanguageMode($languageCode)
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

	public function testUpdateInSingleLanguageMode()
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

	public function testUpdateHooks()
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

}
