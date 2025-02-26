<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageChangeStatusTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageChangeStatus';

	public function testChangeStatusFromDraftToListed(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$this->assertTrue($page->isDraft());

		$listed = $page->changeStatus('listed');

		$this->assertSame('listed', $listed->status());
		$this->assertSame(1, $listed->num());
		$this->assertFalse($listed->parentModel()->drafts()->has($listed));
		$this->assertTrue($listed->parentModel()->children()->listed()->has($listed));
	}

	public function testChangeStatusFromDraftToDraft(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$draft = $page->changeStatus('draft');

		$this->assertSame($draft, $page);
	}

	public function testChangeStatusFromDraftToUnlisted(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$this->assertTrue($page->isDraft());

		$unlisted = $page->changeStatus('unlisted');

		$this->assertSame('unlisted', $unlisted->status());
		$this->assertNull($unlisted->num());
		$this->assertFalse($unlisted->parentModel()->drafts()->has($unlisted));
		$this->assertTrue($unlisted->parentModel()->children()->unlisted()->has($unlisted));
	}

	public function testChangeStatusFromListedToDraft(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$page = $page->changeStatus('listed');

		$this->assertSame('listed', $page->status());
		$this->assertSame(1, $page->num());
		$this->assertFalse($page->isDraft());

		$draft = $page->changeStatus('draft');

		$this->assertTrue($draft->isDraft());
		$this->assertSame('draft', $draft->status());
		$this->assertNull($draft->num());
		$this->assertTrue($draft->parentModel()->drafts()->has($draft));
		$this->assertFalse($draft->parentModel()->children()->listed()->has($draft));
	}

	public function testChangeStatusFromListedToListed(): void
	{
		$page = Page::create([
			'slug'  => 'test',
			'num'   => 1,
			'draft' => false
		]);

		$listed = $page->changeStatus('listed');

		$this->assertSame($listed, $page);
	}

	public function testChangeStatusFromListedToUnlisted(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		$listed = $page->changeStatus('listed');
		$this->assertTrue($listed->isListed());
		$this->assertSame(1, $listed->num());

		$this->assertFalse($listed->parentModel()->children()->unlisted()->has($listed));
		$this->assertTrue($listed->parentModel()->children()->listed()->has($listed));

		$unlisted = $listed->changeStatus('unlisted');

		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertFalse($unlisted->parentModel()->children()->listed()->has($unlisted));
		$this->assertTrue($unlisted->parentModel()->children()->unlisted()->has($unlisted));
	}

	public function testChangeStatusFromUnlistedToListed(): void
	{
		$page = Page::create([
			'slug' => 'test',
		]);

		// change to unlisted
		$unlisted = $page->changeStatus('unlisted');

		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertFalse($unlisted->parentModel()->children()->listed()->has($unlisted));
		$this->assertTrue($unlisted->parentModel()->children()->unlisted()->has($unlisted));

		// change to listed
		$listed = $unlisted->changeStatus('listed');
		$this->assertTrue($listed->isListed());
		$this->assertSame(1, $listed->num());

		$this->assertFalse($listed->parentModel()->children()->unlisted()->has($listed));
		$this->assertTrue($listed->parentModel()->children()->listed()->has($listed));
	}

	public function testChangeStatusFromUnlistedToUnlisted(): void
	{
		$page = Page::create([
			'slug'  => 'test',
			'draft' => false
		]);

		$unlisted = $page->changeStatus('unlisted');

		$this->assertSame($unlisted, $page);
	}

	public function testChangeStatusToDraftHooks(): void
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

	public function testChangeStatusToInvalidStatus(): void
	{
		$page = Page::create([
			'slug' => 'test',
			'blueprint' => [
				'title'  => 'Test',
				'name'   => 'test',
				'status' => [
					'draft'  => 'Draft',
					'listed' => 'Published'
				]
			]
		]);

		$this->assertSame('draft', $page->status());

		$draft = $page->changeStatus('listed');
		$this->assertSame('listed', $draft->status());

		$this->expectException(InvalidArgumentException::class);

		$unlisted = $page->changeStatus('unlisted');
		$this->assertSame('unlisted', $unlisted->status());
	}

	public function testChangeStatusToListedHooks(): void
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

	public function testChangeStatusToUnlistedHooks(): void
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
