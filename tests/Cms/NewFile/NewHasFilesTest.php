<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;

use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

class NewHasFileTraitUser
{
	use HasFiles;

	public function __construct($files)
	{
		$this->files = $files;
	}

	public function files(): Files
	{
		return new Files($this->files);
	}
}

#[CoversClass(HasFiles::class)]
class NewHasFilesTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewHasFiles';

	public static function fileProvider(): array
	{
		return [
			['test.mp3', 'audio', true],
			['test.jpg', 'audio', false],
			['test.json', 'code', true],
			['test.jpg', 'code', false],
			['test.pdf', 'documents', true],
			['test.jpg', 'documents', false],
			['test.jpg', 'images', true],
			['test.mov', 'images', false],
			['test.mov', 'videos', true],
			['test.jpg', 'videos', false],
		];
	}

	public function testCreateFile()
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$parent = $this->app->site();
		$result = $parent->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		$this->assertFileExists($source);
		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.md');
		$this->assertInstanceOf(BaseFile::class, $result->asset());

		// make sure file received UUID right away
		$this->assertIsString($result->content()->get('uuid')->value());
	}

	public function testCreateFileMove()
	{
		$source = static::TMP . '/source.md';

		// create the dummy source
		F::write($source, '# Test');

		$parent = $this->app->site();

		$result = $parent->createFile([
			'filename' => 'test.md',
			'source'   => $source
		], true);

		$this->assertFileDoesNotExist($source);
		$this->assertFileExists($result->root());
		$this->assertFileExists($parent->root() . '/test.md');
		$this->assertInstanceOf(BaseFile::class, $result->asset());

		// make sure file received UUID right away
		$this->assertIsString($result->content()->get('uuid')->value());
	}

	public function testFileWithSlash()
	{
		$page = new Page([
			'slug' => 'mother',
			'children' => [
				[
					'slug' => 'child',
					'files' => [
						['filename' => 'file.jpg']
					]
				]
			]
		]);

		$file = $page->file('child/file.jpg');
		$this->assertSame('mother/child/file.jpg', $file->id());
	}

	#[DataProvider('fileProvider')]
	public function testTypes($filename, $type, $expected)
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$parent = new NewHasFileTraitUser([
			new File(['filename' => $filename, 'parent' => $page])
		]);

		if ($expected === true) {
			$this->assertCount(1, $parent->{$type}());
		} else {
			$this->assertCount(0, $parent->{$type}());
		}
	}

	#[DataProvider('fileProvider')]
	public function testHas($filename, $type, $expected)
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$parent = new NewHasFileTraitUser([
			new File(['filename' => $filename, 'parent' => $page])
		]);

		$this->assertSame($expected, $parent->{'has' . $type}());
	}

	public function testHasFiles()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		// no files
		$parent = new NewHasFileTraitUser([]);

		$this->assertFalse($parent->hasFiles());

		// files
		$parent = new NewHasFileTraitUser([
			new File(['filename' => 'test.jpg', 'parent' => $page])
		]);

		$this->assertTrue($parent->hasFiles());
	}

	public function testFileWithUUID()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$parent = new NewHasFileTraitUser([
			new File([
				'filename' => 'test.jpg',
				'content'  => ['uuid' => 'file-test'],
				'parent'   => $page
			])
		]);

		$this->assertSame('test.jpg', $parent->file('file://file-test')->filename());
	}
}
