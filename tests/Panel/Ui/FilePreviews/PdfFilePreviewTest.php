<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\PdfFilePreview
 */
class PdfFilePreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.pdf', 'parent' => $page]);
		$this->assertTrue(PdfFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(PdfFilePreview::accepts($file));
	}

	/**
	 * @covers ::__construct
	 */
	public function testFactory()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.pdf', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(PdfFilePreview::class, $preview);
		$this->assertSame('k-pdf-file-preview', $preview->component);
	}
}
