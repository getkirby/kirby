<?php

namespace Kirby\Cms;

class PageModelTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/PageModelTest';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.PageModel';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index'  => static::TMP,
				'models' => static::FIXTURES
			]
		]);
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

		$this->assertIsPage($page);
		$this->assertFalse(method_exists($page, 'test'));
	}
}
