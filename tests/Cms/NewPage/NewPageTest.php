<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageTest';

	public function testDepth()
	{
		$grandma = new Page([
			'slug' => 'grandma',
		]);

		$mother = new Page([
			'slug'   => 'mother',
			'parent' => $grandma,
		]);

		$child = new Page([
			'slug'   => 'test',
			'parent' => $mother
		]);

		$this->assertSame(1, $grandma->depth());
		$this->assertSame(2, $mother->depth());
		$this->assertSame(3, $child->depth());
	}
}
