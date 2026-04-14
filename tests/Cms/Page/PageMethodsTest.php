<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageMethodsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageMethods';

	protected function setUp(): void
	{
		parent::setUp();
		Page::$methods = [];
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		Page::$methods = [];
	}

	public function testPageMethod(): void
	{
		Page::$methods = [
			'test' => fn () => 'page method for: ' . $this->slug()
		];

		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('page method for: test', $page->test());
	}
}
