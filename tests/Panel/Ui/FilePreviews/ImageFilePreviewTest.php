<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\ImageFilePreview
 * @covers ::__construct
 */
class ImageFilePreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$this->assertTrue(ImageFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(ImageFilePreview::accepts($file));
	}

	/**
	 * @covers ::details
	 */
	public function testDetails()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$details = $preview->details();

		$detail = array_pop($details);
		$this->assertSame('Orientation', $detail['title']);

		$detail = array_pop($details);
		$this->assertSame('Dimensions', $detail['title']);
	}

	/**
	 * @coversNothing
	 */
	public function testFactory()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(ImageFilePreview::class, $preview);
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.xls', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$props   = $preview->props();
		$this->assertFalse($props['focusable']);
	}
}
