<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

class PageTestModel extends Page
{
	public function test(): string
	{
		return 'test';
	}
}

#[CoversClass(Page::class)]
class PageModelTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageModel';

	public function setUp(): void
	{
		parent::setUp();
		Page::$models = [];
	}

	public function tearDown(): void
	{
		parent::tearDown();
		Page::$models = [];
	}

	public function testModel(): void
	{
		Page::extendModels([
			'test' => PageTestModel::class
		]);

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf(PageTestModel::class, $page);
		$this->assertSame('test', $page->test());
	}

	public function testModelWithUppercaseKey(): void
	{
		Page::extendModels([
			'Test' => PageTestModel::class
		]);

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf(PageTestModel::class, $page);
		$this->assertSame('test', $page->test());
	}

	public function testModelWithDefaultFallback(): void
	{
		Page::extendModels([
			'default' => PageTestModel::class
		]);

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf(PageTestModel::class, $page);
		$this->assertSame('test', $page->test());
	}

	public function testModelWithMissingClass(): void
	{
		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertIsPage($page);
		$this->assertFalse(method_exists($page, 'test'));
	}
}
