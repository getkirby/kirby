<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AudioFilePreview::class)]
class AudioFilePreviewTest extends TestCase
{
	public function testAccepts(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.mp3', 'parent' => $page]);
		$this->assertTrue(AudioFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(AudioFilePreview::accepts($file));
	}

	public function testFactory(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.mp3', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(AudioFilePreview::class, $preview);
		$this->assertSame('k-audio-file-preview', $preview->component);
	}
}
