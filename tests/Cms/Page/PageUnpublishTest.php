<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageUnpublishTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageUnpublish';

	public function testUnpublish(): void
	{
		$page = Page::create([
			'slug' => 'test',
			'draft' => false
		]);

		Page::create([
			'slug' => 'child-a',
			'draft' => false,
			'num' => 1,
			'parent' => $page
		]);

		Page::create([
			'slug' => 'child-b',
			'draft' => false,
			'num' => 2,
			'parent' => $page
		]);

		Page::create([
			'slug' => 'child-c',
			'draft' => false,
			'parent' => $page
		]);

		Page::create([
			'slug' => 'child-d',
			'draft' => true,
			'parent' => $page
		]);

		$listed = $page->children()->listed();
		$unlisted = $page->children()->unlisted();
		$drafts = $page->drafts();

		$this->assertCount(2, $listed);
		foreach ($listed as $child) {
			$this->assertSame('listed', $child->status());
		}

		$this->assertCount(1, $unlisted);
		foreach ($unlisted as $child) {
			$this->assertSame('unlisted', $child->status());
		}

		$this->assertCount(1, $drafts);
		foreach ($drafts as $child) {
			$this->assertSame('draft', $child->status());
		}

		// unpublish all
		foreach ($page->children() as $child) {
			$child->unpublish();
		}

		// make sure that not cached children
		$clone = $page->clone();

		$this->assertCount(0, $clone->children()->listed());
		$this->assertCount(0, $clone->children()->unlisted());
		$this->assertCount(4, $clone->drafts());
	}

}
