<?php

namespace Kirby\Cms;


use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageFilesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageFiles';

	public function testCreateFile(): void
	{
		F::write($source = static::TMP . '/source.md', '');

		$page = Page::create([
			'slug' => 'test'
		]);

		$file = $page->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		$this->assertSame('test.md', $file->filename());
		$this->assertSame('test/test.md', $file->id());
	}

	public function testFiles(): void
	{
		$page = new Page(['slug' => 'test']);
		$this->assertInstanceOf(Files::class, $page->files());
		$this->assertCount(0, $page->files());
	}

	public function testFilesWithValues(): void
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		$this->assertInstanceOf(Files::class, $page->files());
		$this->assertCount(1, $page->files());
	}

	public function testImages(): void
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'test.ai'],
				['filename' => 'test.bmp'],
				['filename' => 'test.gif'],
				['filename' => 'test.eps'],
				['filename' => 'test.ico'],
				['filename' => 'test.jpeg'],
				['filename' => 'test.jpg'],
				['filename' => 'test.jpe'],
				['filename' => 'test.png'],
				['filename' => 'test.ps'],
				['filename' => 'test.psd'],
				['filename' => 'test.svg'],
				['filename' => 'test.tif'],
				['filename' => 'test.tiff'],
				['filename' => 'test.webp'],
				['filename' => 'test.txt'],
				['filename' => 'test.doc'],
			]
		]);

		$this->assertInstanceOf(Files::class, $page->images());
		$this->assertCount(15, $page->images());
	}
}
