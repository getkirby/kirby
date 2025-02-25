<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileManipulateTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileManipulate';

	public function testManipulate(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$this->assertSame(128, $originalFile->width());
		$this->assertSame(128, $originalFile->height());

		$replacedFile = $originalFile->manipulate([
			'width' => 100,
			'height' => 100,
		]);

		$this->assertSame($originalFile->root(), $replacedFile->root());
		$this->assertSame(100, $replacedFile->width());
		$this->assertSame(100, $replacedFile->height());
	}

	public function testManipulateNonImage(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width' => 100,
			'height' => 100,
		]);

		// proves strictly that both are the same object
		$this->assertSame($originalFile, $replacedFile);
	}

	public function testManipulateValidFormat(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$this->assertSame(128, $originalFile->width());
		$this->assertSame(128, $originalFile->height());

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		$this->assertSame('webp', $replacedFile->extension());
		$this->assertSame(100, $replacedFile->width());
		$this->assertSame(100, $replacedFile->height());
	}

	public function testManipulateInvalidValidFormat(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		// proves strictly that both are the same object
		$this->assertSame($originalFile, $replacedFile);
		$this->assertSame('mp4', $replacedFile->extension());
	}
}
