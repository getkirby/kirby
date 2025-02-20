<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageMethodsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageMethods';

	public function setUp(): void
	{
		parent::setUp();
		Page::$methods = [];
	}

	public function tearDown(): void
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
