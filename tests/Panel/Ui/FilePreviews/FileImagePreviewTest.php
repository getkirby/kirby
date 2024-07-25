<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\FileImagePreview
 * @covers ::__construct
 */
class FileImagePreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$this->assertTrue(FileImagePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(FileImagePreview::accepts($file));
	}

	/**
	 * @covers ::details
	 */
	public function testDetails()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new FileImagePreview($file);
		$details = $preview->details();

		$detail = array_pop($details);
		$this->assertSame('Orientation', $detail['title']);

		$detail = array_pop($details);
		$this->assertSame('Dimensions', $detail['title']);
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.xls', 'parent' => $page]);
		$preview = new FileImagePreview($file);
		$props   = $preview->props();
		$this->assertFalse($props['focusable']);
	}
}
