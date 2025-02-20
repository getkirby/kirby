<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPagePublishTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPagePublish';

	public function testPublish(): void
	{
		// main page
		$page = Page::create([
			'slug' => 'test',
		]);

		$site      = $this->app->site();
		$published = $page->publish();

		$this->assertSame('unlisted', $published->status());

		$this->assertFalse($page->parentModel()->drafts()->has($published));
		$this->assertTrue($page->parentModel()->children()->has($published));

		$this->assertFalse($site->drafts()->has($published));
		$this->assertTrue($site->children()->has($published));

		// child
		$child = Page::create([
			'parent' => $page,
			'slug'   => 'child'
		]);

		$published = $child->publish();

		$this->assertSame('unlisted', $published->status());

		$this->assertFalse($child->parentModel()->drafts()->has($published->id()));
		$this->assertTrue($child->parentModel()->children()->has($published->id()));

		$this->assertFalse($page->drafts()->has($published->id()));
		$this->assertTrue($page->children()->has($published->id()));
	}

	public function testPublishAlreadyPublished(): void
	{
		$page = Page::create([
			'slug' => 'test'
		]);

		$page = $page->publish();

		$this->assertSame('unlisted', $page->status());
		$this->assertSame('unlisted', $page->publish()->status());
	}
}
