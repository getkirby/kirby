<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\FilePreview\DefaultFilePreview;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class DummyFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-dummy-file-preview'
	) {
	}

	public static function accepts(File $file): bool
	{
		return $file->type() === 'document';
	}
}

class InvalidFilePreview
{
}

#[CoversClass(FilePreview::class)]
class FilePreviewTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		// authenticate for preview URL
		$this->app->impersonate('kirby');
	}

	public function testDetails(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);
		$details = $preview->details();
		$link    = $file->previewUrl();

		$this->assertSame([
			[
				'title' => 'Template',
				'text'  => '—',
			],
			[
				'title' => 'Media Type',
				'text'  => 'image/jpeg',
			],
			[
				'title' => 'Url',
				'link'  => $link,
				'text'  => $link,
			],
			[
				'title' => 'Size',
				'text' => '0 KB',
			]
		], $details);
	}

	public function testFactory(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.docx', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(DefaultFilePreview::class, $preview);
	}

	public function testFactoryWithCustomHandler(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'filePreviews' => [
				DummyFilePreview::class
			]
		]);

		$page = new Page(['slug' => 'test']);

		$file    = new File(['filename' => 'test.foo', 'parent' => $page]);
		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(DefaultFilePreview::class, $preview);

		$file    = new File(['filename' => 'test.xls', 'parent' => $page]);
		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(DummyFilePreview::class, $preview);
	}

	public function testFactoryWithCustomHandlerInvalid(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'filePreviews' => [
				InvalidFilePreview::class,
				DummyFilePreview::class
			]
		]);

		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('File preview handler "Kirby\Panel\Ui\InvalidFilePreview" must extend Kirby\Panel\Ui\FilePreview');

		FilePreview::factory($file);
	}

	public function testImage(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);
		$image   = $preview->image();

		$this->assertSame('image', $image['icon']);
		$this->assertFalse($image['cover']);
		$this->assertIsString($image['src']);
	}

	public function testProps(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);
		$props   = $preview->props();

		$this->assertIsArray($props['details']);
		$this->assertIsArray($props['image']);
		$this->assertIsString($props['url']);
	}

	public function testRender(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);

		$this->assertSame('k-dummy-file-preview', $preview->render()['component']);
	}
}
