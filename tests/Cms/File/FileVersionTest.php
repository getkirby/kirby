<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileVersion::class)]
class FileVersionTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.FileVersion';

	public function testConstruct(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$original = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => [
				'title' => 'Test Title'
			]
		]);

		$version = new FileVersion([
			'original'      => $original,
			'url'           => $url = 'https://assets.getkirby.com/test-200x200.jpg',
			'modifications' => $mods = [
				'width' => 200,
				'height' => 200
			]
		]);

		$this->assertSame($url, $version->url());
		$this->assertSame('Test Title', $version->title()->value());
		$this->assertSame($mods, $version->modifications());
		$this->assertSame($original, $version->original());
		$this->assertSame($original->kirby(), $version->kirby());
	}

	public function testExists(): void
	{
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		$original = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$version = new FileVersion([
			'original' => $original,
			'root'     => static::TMP . '/test-version.jpg',
			'modifications' => [
				'width' => 50,
			]
		]);

		$this->assertFalse($version->exists());

		// calling an asset method will trigger
		// `FileVersion::save()` if it doesn't
		// exist yet
		$this->assertSame(50, $version->width());

		$this->assertTrue($version->exists());
	}

	public function testToArray(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$original = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => [
				'title' => 'Test Title'
			]
		]);

		$version = new FileVersion([
			'original' => $original,
			'root'     => static::FIXTURES . '/test.jpg'
		]);

		$this->assertSame('jpg', $version->toArray()['extension']);
		$this->assertSame(1192, $version->toArray()['size']);
	}

	public function testToString(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$original = new File([
			'filename' => 'test.jpg',
			'parent' => $page,
			'content' => ['alt' => 'Test text']
		]);
		$version  = new FileVersion([
			'original' => $original,
			'root'     => static::FIXTURES . '/test.txt',
			'url'      => $url = 'https://assets.getkirby.com/test-200x200.txt',
		]);

		$this->assertSame($url, (string)$version);

		$version  = new FileVersion([
			'original' => $original,
			'root'     => static::FIXTURES . '/test.jpg',
			'url'      => $url = 'https://assets.getkirby.com/test-200x200.jpg',
		]);

		$this->assertSame('<img alt="Test text" src="' . $url . '">', (string)$version);
	}
}
