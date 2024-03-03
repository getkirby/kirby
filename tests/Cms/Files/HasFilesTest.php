<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Filesystem\File as BaseFile;

class HasFileTraitUser
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

class HasFilesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.HasFiles';

	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email' => 'admin@domain.com',
					'role'  => 'admin'
				]
			],
			'user' => 'admin@domain.com'
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

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

	/**
	 * @dataProvider fileProvider
	 */
	public function testTypes($filename, $type, $expected)
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$parent = new HasFileTraitUser([
			new File(['filename' => $filename, 'parent' => $page])
		]);

		if ($expected === true) {
			$this->assertCount(1, $parent->{$type}());
		} else {
			$this->assertCount(0, $parent->{$type}());
		}
	}

	/**
	 * @dataProvider fileProvider
	 */
	public function testHas($filename, $type, $expected)
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$parent = new HasFileTraitUser([
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
		$parent = new HasFileTraitUser([]);

		$this->assertFalse($parent->hasFiles());

		// files
		$parent = new HasFileTraitUser([
			new File(['filename' => 'test.jpg', 'parent' => $page])
		]);

		$this->assertTrue($parent->hasFiles());
	}

	public function testFileWithUUID()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$parent = new HasFileTraitUser([
			new File([
				'filename' => 'test.jpg',
				'content'  => ['uuid' => 'file-test'],
				'parent'   => $page
			])
		]);

		$this->assertSame('test.jpg', $parent->file('file://file-test')->filename());
	}
}
