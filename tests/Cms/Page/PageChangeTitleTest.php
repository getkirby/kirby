<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChangeTitleTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageChangeTitle';

	public function testChangeTitle(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$this->assertSame('test', $page->title()->value());

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$modified = $page->changeTitle($title = 'Modified Title');

		$this->assertSame($title, $modified->title()->value());

		$this->assertSame($modified, $drafts->find('test'));
		$this->assertSame($modified, $childrenAndDrafts->find('test'));
	}

	public function testChangeTitleWhenChangesExist()
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		// save the original title
		$page->version('latest')->save([
			'title' => 'Old Title'
		]);

		// add some changes
		$page->version('changes')->save([
			'text' => 'Some additional text'
		]);

		$modified = $page->changeTitle('New Title');

		$this->assertSame('New Title', $modified->title()->value());

		$changes = $modified->version('changes')->content();

		$this->assertSame('New Title', $changes->get('title')->value(), 'The title should be updated in the changes version');
		$this->assertSame('Some additional text', $changes->get('text')->value(), 'Other changes should remain the same');
	}

	public function testChangeTitleHooks(): void
	{
		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.changeTitle:before' => function (Page $page, $title, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertSame('test', $page->title()->value());
					$phpunit->assertSame('New Title', $title);
					$phpunit->assertNull($languageCode);
					$calls++;
				},
				'page.changeTitle:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
					$phpunit->assertSame('New Title', $newPage->title()->value());
					$phpunit->assertSame('test', $oldPage->title()->value());
					$calls++;
				}
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
		]);

		$page->changeTitle('New Title');

		$this->assertSame(2, $calls);
	}

	public function testChangeTitleBeforeHookDefaultLanguage(): void
	{
		$this->setupMultiLanguage();

		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.changeTitle:before' => function (Page $page, $title, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertSame('test', $page->title()->value);
					$phpunit->assertSame('New Title', $title);
					$phpunit->assertNull($languageCode);
					$calls++;
				},
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test'
		]);

		$page->changeTitle('New Title');

		$this->assertSame(1, $calls);
	}

	public function testChangeTitleBeforeHookSecondaryLanguage(): void
	{
		$this->setupMultiLanguage();

		$calls = 0;
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.changeTitle:before' => function (Page $page, $title, $languageCode) use ($phpunit, &$calls) {
					$phpunit->assertSame('test', $page->title()->value);
					$phpunit->assertSame('New Title', $title);
					$phpunit->assertSame('de', $languageCode);
					$calls++;
				},
			]
		]);

		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$page = Page::create([
			'slug' => 'test'
		]);

		$page->changeTitle('New Title', 'de');

		$this->assertSame(1, $calls);
	}

}
