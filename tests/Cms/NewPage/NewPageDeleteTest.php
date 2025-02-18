<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageDeleteTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageDeleteTest';

	public function setUp(): void
	{
		parent::setUp();

		$this->app->impersonate('kirby');
	}

	public function site()
	{
		return $this->app->site();
	}

	public function testDeleteDraft()
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$this->assertTrue($page->exists());
		$this->assertTrue($page->parentModel()->drafts()->has($page));

		$page->delete();

		$this->assertFalse($page->exists());
		$this->assertFalse($page->parentModel()->drafts()->has($page));
	}

	public function testDeleteHooks()
	{
		$calls = 0;
		$phpunit  = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.delete:before' => function ($page, $force) use ($phpunit, &$calls) {
					$phpunit->assertIsPage($page);
					$phpunit->assertFalse($force);
					$phpunit->assertFileExists($page->root());
					$calls++;
				},
				'page.delete:after' => function ($status, $page) use ($phpunit, &$calls) {
					$phpunit->assertTrue($status);
					$phpunit->assertIsPage($page);
					$phpunit->assertFileDoesNotExist($page->root());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		$page = Page::create([
			'slug' => 'test'
		]);

		$page->delete();

		$this->assertSame(2, $calls);
	}

	public function testDeletePage()
	{
		$page = Page::create([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertTrue($page->exists());
		$this->assertTrue($page->parentModel()->children()->has($page));

		$page->delete();

		$this->assertFalse($page->exists());
		$this->assertFalse($page->parentModel()->children()->has($page));
	}

	public function testDeleteMultipleSortedPages()
	{
		$range = range(1, 10);
		$site  = $this->site();

		foreach ($range as $num) {
			$page = Page::create([
				'slug' => Str::random(),
				'num'  => $num
			]);

			$this->assertTrue($page->exists());
			$this->assertTrue($site->children()->has($page));
		}

		foreach ($site->children() as $page) {
			$page->delete();

			$this->assertFalse($page->exists());
			$this->assertFalse($site->children()->has($page));
		}

		$this->assertCount(0, $site->children());
	}

}
