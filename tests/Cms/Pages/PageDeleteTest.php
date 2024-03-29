<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\Str;

class PageDeleteTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageDelete';

	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		$this->app->impersonate('kirby');

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
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
