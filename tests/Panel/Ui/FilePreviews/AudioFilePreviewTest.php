<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreviews\AudioFilePreview
 */
class AudioFilePreviewTest extends TestCase
{
	/**
	 * @covers ::accepts
	 */
	public function testAccepts()
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.mp3', 'parent' => $page]);
		$this->assertTrue(AudioFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(AudioFilePreview::accepts($file));
	}

	/**
	 * @covers ::__construct
	 */
	public function testFactory()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.mp3', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(AudioFilePreview::class, $preview);
		$this->assertSame('k-audio-file-preview', $preview->component);
	}
}
