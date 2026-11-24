<?php

namespace Kirby\Panel\Ui\FilePreview;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VideoFilePreview::class)]
class VideoFilePreviewTest extends TestCase
{
	public function testAccepts(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.mp4', 'parent' => $page]);
		$this->assertTrue(VideoFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(VideoFilePreview::accepts($file));
	}

	public function testFactory(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.mp4', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(VideoFilePreview::class, $preview);
		$this->assertSame('k-video-file-preview', $preview->component);
	}
}
