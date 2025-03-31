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
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page     = $parent->draft('child-c');
		$children = $parent->childrenAndDrafts();

		$this->assertTrue($page->isDraft());
		$this->assertTrue($parent->drafts()->has($page));
		$this->assertFalse($parent->children()->listed()->has($page));
		$this->assertSame('child-a', $children->nth(0)->slug());
		$this->assertSame('child-b', $children->nth(1)->slug());
		$this->assertSame('child-c', $children->nth(2)->slug());

		$listed   = $page->changeStatus('listed');
		$children = $parent->childrenAndDrafts();

		$this->assertSame('listed', $listed->status());
		$this->assertSame(2, $listed->num());

		$this->assertFalse($parent->drafts()->has($page));
		$this->assertTrue($parent->children()->listed()->has($page));
		$this->assertSame('child-a', $children->nth(0)->slug());
		$this->assertSame('child-c', $children->nth(1)->slug());
		$this->assertSame('child-b', $children->nth(2)->slug());
	}

	public function testChangeStatusFromDraftToDraft(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->draft('child-c');

		$this->assertTrue($page->isDraft());
		$this->assertTrue($parent->drafts()->has($page));
		$this->assertFalse($parent->children()->listed()->has($page));

		$draft    = $page->changeStatus('draft');
		$children = $parent->childrenAndDrafts();

		$this->assertSame($draft, $page);
		$this->assertTrue($parent->drafts()->has($page));
		$this->assertFalse($parent->children()->listed()->has($page));
		$this->assertSame('child-a', $children->nth(0)->slug());
		$this->assertSame('child-b', $children->nth(1)->slug());
		$this->assertSame('child-c', $children->nth(2)->slug());
	}

	public function testChangeStatusFromDraftToUnlisted(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->draft('child-c');

		$this->assertTrue($page->isDraft());
		$this->assertTrue($parent->drafts()->has($page));
		$this->assertFalse($parent->children()->unlisted()->has($page));

		$unlisted = $page->changeStatus('unlisted');
		$children = $parent->childrenAndDrafts();

		$this->assertSame('unlisted', $unlisted->status());
		$this->assertNull($unlisted->num());
		$this->assertFalse($parent->drafts()->has($unlisted));
		$this->assertTrue($parent->children()->unlisted()->has($unlisted));
		$this->assertSame('child-a', $children->nth(0)->slug());
		$this->assertSame('child-b', $children->nth(1)->slug());
		$this->assertSame('child-c', $children->nth(2)->slug());
	}

	public function testChangeStatusFromListedToDraft(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->find('child-a');

		$this->assertSame(1, $page->num());
		$this->assertFalse($parent->drafts()->has($page));
		$this->assertTrue($parent->children()->listed()->has($page));

		$page = $page->changeStatus('listed');

		$this->assertSame('listed', $page->status());
		$this->assertSame(1, $page->num());
		$this->assertFalse($page->isDraft());

		$draft    = $page->changeStatus('draft');
		$children = $parent->childrenAndDrafts();

		$this->assertTrue($draft->isDraft());
		$this->assertSame('draft', $draft->status());
		$this->assertNull($draft->num());
		$this->assertTrue($parent->drafts()->has($draft));
		$this->assertFalse($parent->children()->listed()->has($draft));
		$this->assertSame('child-b', $children->nth(0)->slug());
		$this->assertSame('child-c', $children->nth(1)->slug());
		$this->assertSame('child-a', $children->nth(2)->slug());
	}

	public function testChangeStatusFromListedToListed(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->find('child-a');

		$this->assertSame(1, $page->num());
		$this->assertFalse($parent->drafts()->has($page));
		$this->assertTrue($parent->children()->listed()->has($page));

		$listed = $page->changeStatus('listed');

		$this->assertSame($listed, $page);
		$this->assertFalse($parent->drafts()->has($page));
		$this->assertTrue($parent->children()->listed()->has($page));
	}

	public function testChangeStatusFromListedToUnlisted(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->find('child-a');

		$this->assertFalse($parent->children()->unlisted()->has($page));
		$this->assertTrue($parent->children()->listed()->has($page));

		$listed = $page->changeStatus('listed');
		$this->assertTrue($listed->isListed());
		$this->assertSame(1, $listed->num());

		$this->assertFalse($parent->children()->unlisted()->has($listed));
		$this->assertTrue($parent->children()->listed()->has($listed));

		$unlisted = $listed->changeStatus('unlisted');

		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertFalse($parent->children()->listed()->has($unlisted));
		$this->assertTrue($parent->children()->unlisted()->has($unlisted));
	}

	public function testChangeStatusFromUnlistedToListed(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->find('child-b');

		$this->assertTrue($parent->children()->unlisted()->has($page));
		$this->assertFalse($parent->children()->listed()->has($page));

		// change to unlisted
		$unlisted = $page->changeStatus('unlisted');

		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertFalse($parent->children()->listed()->has($unlisted));
		$this->assertTrue($parent->children()->unlisted()->has($unlisted));

		// change to listed
		$listed = $unlisted->changeStatus('listed');
		$this->assertTrue($listed->isListed());
		$this->assertSame(2, $listed->num());

		$this->assertFalse($parent->children()->unlisted()->has($listed));
		$this->assertTrue($parent->children()->listed()->has($listed));
	}

	public function testChangeStatusFromUnlistedToUnlisted(): void
	{
		$parent = Page::create([
			'slug'     => 'test',
			'children' => [
				[
					'slug' => 'child-a',
					'num'  => 1
				],
				[
					'slug' => 'child-b',
					'num'  => null
				]
			],
			'drafts' => [
				[
					'slug'  => 'child-c',
					'draft' => true
				]
			]
		]);

		$page = $parent->find('child-b');

		$this->assertTrue($parent->children()->unlisted()->has($page));
		$this->assertFalse($parent->children()->listed()->has($page));

		$unlisted = $page->changeStatus('unlisted');

		$this->assertSame($unlisted, $page);
		$this->assertTrue($unlisted->isUnlisted());
		$this->assertNull($unlisted->num());

		$this->assertTrue($parent->children()->unlisted()->has($unlisted));
		$this->assertFalse($parent->children()->listed()->has($unlisted));
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
