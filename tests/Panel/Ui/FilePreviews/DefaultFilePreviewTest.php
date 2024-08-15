<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\DefaultFilePreview
 * @covers ::__construct
 */
class DefaultFilePreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertTrue(DefaultFilePreview::accepts($file));
	}

	/**
	 * @covers ::image
	 * @covers ::props
	 */
	public function testProps()
	{
		$page      = new Page(['slug' => 'test']);
		$file      = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$component = new DefaultFilePreview($file);
		$props     = $component->props();

		$this->assertIsArray($props['image']);
	}
}
