<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Page::class)]
class NewPageNumTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageNumTest';

	public function testNum()
	{
		$page = new Page([
			'slug' => 'test',
			'num'  => 1,
		]);

		$this->assertSame(1, $page->num());
	}

	public function testNumWithInvalidValue()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'num'  => []
		]);
	}

	public function testNumWithEmptyValue()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertNull($page->num());
	}
}
