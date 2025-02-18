<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageChangeStatusTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageChangeStatusTest';

	public function testChangeStatusToDraftHooks()
	{
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.changeStatus:before' => function (Page $page, $status, $position) use ($phpunit) {
					$phpunit->assertSame('draft', $status);
					$phpunit->assertNull($position);
				},
				'page.changeStatus:after' => function (Page $newPage, Page $oldPage) use ($phpunit) {
					$phpunit->assertSame('listed', $oldPage->status());
					$phpunit->assertSame('draft', $newPage->status());
				}
			],
		]);

		$app->impersonate('kirby');

		$page = Page::create([
			'slug'  => 'test',
			'num'   => 1,
			'draft' => false
		]);

		$children          = $app->site()->children();
		$drafts            = $app->site()->drafts();
		$childrenAndDrafts = $app->site()->childrenAndDrafts();

		$this->assertIsPage($page, $children->find('test'));

		$newPage = $page->changeStatus('draft');

		$this->assertSame($newPage, $drafts->find('test'));
		$this->assertNull($children->find('test'));
		$this->assertSame($newPage, $childrenAndDrafts->find('test'));
	}

	public function testChangeStatusToListedHooks()
	{
		$phpunit = $this;
		$before  = 0;
		$after   = 0;

		$app = $this->app->clone([
			'hooks' => [
				'page.changeStatus:before' => function (Page $page, $status, $position) use (&$before, $phpunit) {
					$phpunit->assertSame('listed', $status);
					$phpunit->assertSame($before + 1, $position);
					$before++;
				},
				'page.changeStatus:after' => function (Page $newPage, Page $oldPage) use (&$after, $phpunit) {
					$phpunit->assertSame('draft', $oldPage->status());
					$phpunit->assertSame('listed', $newPage->status());
					$after++;
				}
			]
		]);

		$app->impersonate('kirby');

		$pageA = Page::create(['slug' => 'test-a', 'num' => null]);
		$pageB = Page::create(['slug' => 'test-b', 'num' => null]);

		$children          = $app->site()->children();
		$drafts            = $app->site()->drafts();
		$childrenAndDrafts = $app->site()->childrenAndDrafts();

		$this->assertIsPage($pageA, $drafts->find('test-a'));

		$newPageA = $pageA->changeStatus('listed');
		$newPageB = $pageB->changeStatus('listed');

		$this->assertSame(2, $before);
		$this->assertSame(2, $after);

		$this->assertSame($newPageA, $children->find('test-a'));
		$this->assertNull($drafts->find('test-a'));
		$this->assertSame($newPageA, $childrenAndDrafts->find('test-a'));
	}

	public function testChangeStatusToUnlistedHooks()
	{
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.changeStatus:before' => function (Page $page, $status, $position) use ($phpunit) {
					$phpunit->assertSame('unlisted', $status);
					$phpunit->assertNull($position);
				},
				'page.changeStatus:after' => function (Page $newPage, Page $oldPage) use ($phpunit) {
					$phpunit->assertSame('draft', $oldPage->status());
					$phpunit->assertSame('unlisted', $newPage->status());
				}
			]
		]);

		$app->impersonate('kirby');

		$page = Page::create(['slug' => 'test']);

		$children          = $app->site()->children();
		$drafts            = $app->site()->drafts();
		$childrenAndDrafts = $app->site()->childrenAndDrafts();

		$this->assertIsPage($page, $drafts->find('test'));

		$newPage = $page->changeStatus('unlisted');

		$this->assertSame($newPage, $children->find('test'));
		$this->assertNull($drafts->find('test'));
		$this->assertSame($newPage, $childrenAndDrafts->find('test'));
	}

}
