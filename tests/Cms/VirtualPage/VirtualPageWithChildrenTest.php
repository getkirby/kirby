<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

class VirtualPageWithChildren extends Page
{
	public function children(): Pages
	{
		return new Pages([
			new VirtualPage(['slug' => 'child1', 'content' => ['title' => 'Child 1']]),
			new VirtualPage(['slug' => 'child2', 'content' => ['title' => 'Child 2']]),
		]);
	}
}

#[CoversClass(Page::class)]
class VirtualPageWithChildrenTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.VirtualPageWithChildren';

	public function testChildren()
	{
		$page = new VirtualPageWithChildren([
			'slug' => 'mother'
		]);

		$this->assertCount(2, $page->children());
		$this->assertSame('Child 1', $page->children()->first()->title()->value());
		$this->assertSame('Child 2', $page->children()->last()->title()->value());
	}
}
