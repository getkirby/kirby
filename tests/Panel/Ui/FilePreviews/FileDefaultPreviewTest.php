<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\FileDefaultPreview
 * @covers ::__construct
 */
class FileDefaultPreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->assertTrue(FileDefaultPreview::accepts($file));
	}

	/**
	 * @covers ::image
	 * @covers ::props
	 */
	public function testProps()
	{
		$page      = new Page(['slug' => 'test']);
		$file      = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$component = new FileDefaultPreview($file);
		$props     = $component->props();

		$this->assertSame('image', $props['image']['icon']);
		$this->assertFalse($props['image']['cover']);
		$this->assertIsString($props['image']['src']);
	}
}
