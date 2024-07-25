<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Panel\Ui\FilePreviews\FileDefaultPreview;
use Kirby\TestCase;

class FileDummyPreview extends FilePreview
{
	public static function accepts(File $file): bool
	{
		return $file->type() === 'document';
	}
}

/**
 * @coversDefaultClass \Kirby\Panel\Ui\FilePreview
 * @covers ::__construct
 */
class FilePreviewTest extends TestCase
{
	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$page = new Page(['slug' => 'test']);
		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$component = FilePreview::factory($file);
		$this->assertInstanceOf(FileDefaultPreview::class, $component);
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
				FileDummyPreview::class
			]
		]);

		$page = new Page(['slug' => 'test']);

		$file      = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$component = FilePreview::factory($file);
		$this->assertInstanceOf(FileDefaultPreview::class, $component);

		$file      = new File(['filename' => 'test.xls', 'parent' => $page]);
		$component = FilePreview::factory($file);
		$this->assertInstanceOf(FileDummyPreview::class, $component);
	}

	/**
	 * @covers ::details
	 * @covers ::props
	 */
	public function testProps()
	{
		$page      = new Page(['slug' => 'test']);
		$file      = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$component = new FileDummyPreview($file);

		$this->assertSame([
			'details' => [
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
		            'text'  => 'test/test.jpg',
		            'link'  => '/test/test.jpg',
		        ],
		        [
		            'title' => 'Size',
		            'text' => '0 KB',
		        ]
			],
			'url' => '/test/test.jpg'
		], $component->props());
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$page      = new Page(['slug' => 'test']);
		$file      = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$component = new FileDummyPreview($file);

		$this->assertSame('k-file-default-preview', $component->render()['component']);
	}
}
