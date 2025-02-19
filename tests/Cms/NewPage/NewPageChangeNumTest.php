<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageChangeNumTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageChangeNumTest';

	public function testChangeNum()
	{
		$site = $this->app->site();

		$page = Page::create([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertSame(1, $page->num());
		$this->assertSame('1_test', $page->dirname());
		$this->assertSame(1, $page->parentModel()->find('test')->num());
		$this->assertSame(1, $site->find('test')->num());

		$page = $page->changeNum(2);

		$this->assertSame(2, $page->num());
		$this->assertSame('2_test', $page->dirname());
		$this->assertSame(2, $site->find('test')->num());
	}

	public function testChangeNumHooks()
	{
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.changeNum:before' => function ($page, $num) use ($phpunit) {
					$phpunit->assertSame(2, $num);
				},
				'page.changeNum:after' => function ($newPage, $oldPage) use ($phpunit) {
					$phpunit->assertSame(1, $oldPage->num());
					$phpunit->assertSame(2, $newPage->num());
				}
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
			'num'  => 1
		]);

		$children          = $this->app->site()->children();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		$updatedPage = $page->changeNum(2);

		$this->assertNotSame($page, $updatedPage);
		$this->assertSame(2, $updatedPage->num());

		$this->assertIsPage($updatedPage, $children->find('test'));
		$this->assertIsPage($updatedPage, $childrenAndDrafts->find('test'));
	}

	public function testChangeNumWhenNumStaysTheSame()
	{
		$this->app = $this->app->clone([
			'hooks' => [
				'page.changeNum:before' => function () {
					throw new Exception('This should not be called');
				}
			]
		]);

		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test',
			'num'  => 1
		]);

		$children          = $this->app->site()->children();
		$childrenAndDrafts = $this->app->site()->childrenAndDrafts();

		// the result page should stay the same
		$this->assertIsPage($page->changeNum(1), $page);

		$this->assertIsPage($page, $children->find('test'));
		$this->assertIsPage($page, $childrenAndDrafts->find('test'));
	}

}
