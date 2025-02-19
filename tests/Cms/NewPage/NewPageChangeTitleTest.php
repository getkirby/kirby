<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageChangeTitleTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageChangeTitleTest';

	public function testChangeTitle()
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->title()->value());

		$drafts            = $this->app->site()->drafts();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$modified = $page->changeTitle($title = 'Modified Title');

		$this->assertSame($title, $modified->title()->value());

		$this->assertSame($modified, $drafts->find('test'));
		$this->assertSame($modified, $childrenAndDrafts->find('test'));
	}

	public function testChangeTitleHooks()
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

	public function testChangeTitleBeforeHookDefaultLanguage()
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

	public function testChangeTitleBeforeHookSecondaryLanguage()
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
