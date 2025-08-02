<?php

namespace Kirby\Panel\Ui\FilePreview;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ImageFilePreview::class)]
class ImageFilePreviewTest extends TestCase
{
	public function testAccepts(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$this->assertTrue(ImageFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(ImageFilePreview::accepts($file));
	}

	public function testDetails(): void
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

	public function testFactory(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(ImageFilePreview::class, $preview);
		$this->assertSame('k-image-file-preview', $preview->component);
	}

	public function testProps(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.xls', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$props   = $preview->props();
		$this->assertFalse($props['focusable']);
	}
}
