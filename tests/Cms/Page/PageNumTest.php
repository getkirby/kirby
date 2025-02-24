<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class PageNumTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageNum';

	public function testNum(): void
	{
		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
		]);

		$this->assertSame(1, $page->num());
	}

	public function testNumWithInvalidValue(): void
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'num'  => []
		]);
	}

	public function testNumWithEmptyValue(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertNull($page->num());
	}
}
