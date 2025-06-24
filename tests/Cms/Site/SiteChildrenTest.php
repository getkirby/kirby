<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Site::class)]
class SiteChildrenTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SiteChildren';

	public function testChildren(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'foo',
					'template' => 'article'
				]
			]
		]);
		$this->assertInstanceOf(Pages::class, $site->children());
		$this->assertCount(1, $site->children());
		$this->assertSame('foo', $site->children()->first()->slug());
	}

	public function testChildrenDefault(): void
	{
		$site = new Site();
		$this->assertInstanceOf(Pages::class, $site->children());
		$this->assertCount(0, $site->children());
	}

	public function testChildrenInvalid(): void
	{
		$this->expectException(TypeError::class);
		new Site(['children' => 'children']);
	}

	public function testCreateChild(): void
	{
		$site = new Site();
		$page = $site->createChild([
			'slug'     => 'test',
			'template' => 'test',
		]);

		$this->assertSame('test', $page->slug());
		$this->assertSame('test', $page->intendedTemplate()->name());
	}
}
