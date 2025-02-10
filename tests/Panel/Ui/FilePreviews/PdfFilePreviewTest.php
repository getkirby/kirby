<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PdfFilePreview::class)]
class PdfFilePreviewTest extends TestCase
{
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.pdf', 'parent' => $page]);
		$this->assertTrue(PdfFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(PdfFilePreview::accepts($file));
	}

	public function testFactory()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.pdf', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(PdfFilePreview::class, $preview);
		$this->assertSame('k-pdf-file-preview', $preview->component);
	}
}
