<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\VideoFilePreview
 */
class VideoFilePreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.mp4', 'parent' => $page]);
		$this->assertTrue(VideoFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(VideoFilePreview::accepts($file));
	}

	/**
	 * @covers ::__construct
	 */
	public function testFactory()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.mp4', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(VideoFilePreview::class, $preview);
		$this->assertSame('k-video-file-preview', $preview->component);
	}
}
