<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

class NewPageTestModel extends Page
{
	public function test(): string
	{
		return 'test';
	}
}

#[CoversClass(Page::class)]
class NewPageModelTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageModelTest';

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

	public function testModel()
	{
		Page::$models = [
			'test' => NewPageTestModel::class
		];

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf(NewPageTestModel::class, $page);
		$this->assertSame('test', $page->test());
	}

	public function testModelWithDefaultFallback()
	{
		Page::$models = [
			'default' => NewPageTestModel::class
		];

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertInstanceOf(NewPageTestModel::class, $page);
		$this->assertSame('test', $page->test());
	}

	public function testModelWithMissingClass()
	{
		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'test'
		]);

		$this->assertIsPage($page);
		$this->assertFalse(method_exists($page, 'test'));
	}
}
