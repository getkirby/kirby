<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\FilePreviews\DefaultFilePreview;
use Kirby\TestCase;

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

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreview
 * @covers ::__construct
 */
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

	/**
	 * @covers ::details
	 */
	public function testDetails()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);
		$details = $preview->details();

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
				'text'  => '/test/test.jpg',
				'link'  => '/test/test.jpg',
			],
			[
				'title' => 'Size',
				'text' => '0 KB',
			]
		], $details);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.docx', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(DefaultFilePreview::class, $preview);
	}

	/**
	 * @covers ::factory
	 */
	public function testFactoryWithCustomHandler()
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

	/**
	 * @covers ::factory
	 */
	public function testFactoryWithCustomHandlerInvalid()
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

	/**
	 * @covers ::image
	 */
	public function testImage()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);
		$image   = $preview->image();

		$this->assertSame('image', $image['icon']);
		$this->assertFalse($image['cover']);
		$this->assertIsString($image['src']);
	}

	/**
	 * @covers ::props
	 */
	public function testProps()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);
		$props   = $preview->props();

		$this->assertIsArray($props['details']);
		$this->assertIsArray($props['image']);
		$this->assertIsString($props['url']);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new DummyFilePreview($file);

		$this->assertSame('k-dummy-file-preview', $preview->render()['component']);
	}
}
