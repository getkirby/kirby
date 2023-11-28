<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

class PageModelTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index'  => $this->tmp = __DIR__ . '/tmp',
				'models' => __DIR__ . '/fixtures/PageModelTest'
			]
		]);

		Dir::make($this->tmp);
	}

	public function testPageModelWithTemplate()
	{
		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'article',
		]);

		$this->assertInstanceOf(\ArticlePage::class, $page);
		$this->assertSame('test', $page->test());
	}

	public function testDefaultPageModel()
	{
		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'non-existing',
		]);

		$this->assertInstanceOf(\DefaultPage::class, $page);
		$this->assertSame('bar', $page->foo());
	}

	public function testMissingPageModel()
	{
		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'project',
		]);

		$this->assertInstanceOf(Page::class, $page);
		$this->assertFalse(method_exists($page, 'test'));
	}
}
