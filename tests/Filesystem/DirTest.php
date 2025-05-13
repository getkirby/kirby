<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase as TestCase;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Filesystem\Dir
 */
class DirTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/dir';
	public const TMP      = KIRBY_TMP_DIR . '/Filesystem.Dir';

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	protected function create(array $items, ...$args)
	{
		foreach ($items as $item) {
			$root = static::TMP . '/' . $item;

			if ($extension = F::extension($item)) {
				F::write($root, '');
			} else {
				Dir::make($root);
			}
		}

		return Dir::inventory(static::TMP, ...$args);
	}

	/**
	 * @covers ::copy
	 */
	public function testCopy()
	{
		$src    = static::FIXTURES . '/copy';
		$target = static::TMP . '/copy';

		$result = Dir::copy($src, $target);

		$this->assertTrue($result);

		$this->assertFileExists($target . '/a.txt');
		$this->assertFileExists($target . '/subfolder/b.txt');
		$this->assertFileDoesNotExist($target . '/subfolder/.gitignore');
	}

	/**
	 * @covers ::copy
	 */
	public function testCopyNonRecursive()
	{
		$src    = static::FIXTURES . '/copy';
		$target = static::TMP . '/copy';

		$result = Dir::copy($src, $target, false);

		$this->assertTrue($result);

		$this->assertFileExists($target . '/a.txt');
		$this->assertFileDoesNotExist($target . '/subfolder/b.txt');
		$this->assertFileDoesNotExist($target . '/subfolder/.gitignore');
	}

	/**
	 * @covers ::copy
	 */
	public function testCopyIgnore()
	{
		$src    = static::FIXTURES . '/copy';
		$target = static::TMP . '/copy';

		$result = Dir::copy($src, $target, true, [$src . '/subfolder/b.txt']);

		$this->assertTrue($result);

		$this->assertFileExists($target . '/a.txt');
		$this->assertDirectoryExists($target . '/subfolder');
		$this->assertFileDoesNotExist($target . '/subfolder/b.txt');
		$this->assertFileDoesNotExist($target . '/subfolder/.gitignore');
	}

	/**
	 * @covers ::copy
	 */
	public function testCopyNoIgnore()
	{
		$src    = static::FIXTURES . '/copy';
		$target = static::TMP . '/copy';

		$result = Dir::copy($src, $target, true, false);

		$this->assertTrue($result);

		$this->assertFileExists($target . '/a.txt');
		$this->assertDirectoryExists($target . '/subfolder');
		$this->assertFileExists($target . '/subfolder/b.txt');
		$this->assertFileExists($target . '/subfolder/.gitignore');
	}

	/**
	 * @covers ::copy
	 */
	public function testCopyMissingSource()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('The directory "/does-not-exist" does not exist');

		$src    = '/does-not-exist';
		$target = static::TMP . '/copy';

		Dir::copy($src, $target);
	}

	/**
	 * @covers ::copy
	 */
	public function testCopyExistingTarget()
	{
		$src    = static::FIXTURES . '/copy';
		$target = static::FIXTURES . '/copy';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The target directory "' . $target . '" exists');

		Dir::copy($src, $target);
	}

	/**
	 * @covers ::copy
	 */
	public function testCopyInvalidTarget()
	{
		$src    = static::FIXTURES . '/copy';
		$target = '';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The target directory "' . $target . '" could not be created');

		Dir::copy($src, $target);
	}

	/**
	 * @covers ::exists
	 */
	public function testExists()
	{
		$this->assertFalse(Dir::exists(static::TMP));
		Dir::make(static::TMP);
		$this->assertTrue(Dir::exists(static::TMP));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsIn()
	{
		$test = static::TMP . '/test';

		$this->assertFalse(Dir::exists($test, static::TMP));
		Dir::make($test);
		$this->assertTrue(Dir::exists($test, static::TMP));
		$this->assertTrue(Dir::exists(static::TMP . '/../Filesystem.Dir/test', static::TMP));
		$this->assertTrue(Dir::exists($test, dirname(static::TMP)));
		$this->assertFalse(Dir::exists($test, static::FIXTURES));
	}

	/**
	 * @covers ::index
	 */
	public function testIndex()
	{
		Dir::make($dir = static::TMP);
		Dir::make(static::TMP . '/sub');

		F::write(static::TMP . '/a.txt', 'test');
		F::write(static::TMP . '/b.txt', 'test');

		$expected = [
			'a.txt',
			'b.txt',
			'sub',
		];

		$this->assertSame($expected, Dir::index($dir));
	}

	/**
	 * @covers ::index
	 */
	public function testIndexRecursive()
	{
		Dir::make($dir = static::TMP);
		Dir::make(static::TMP . '/sub');
		Dir::make(static::TMP . '/sub/sub');

		F::write(static::TMP . '/a.txt', 'test');
		F::write(static::TMP . '/sub/b.txt', 'test');
		F::write(static::TMP . '/sub/sub/c.txt', 'test');

		$expected = [
			'a.txt',
			'sub',
			'sub/b.txt',
			'sub/sub',
			'sub/sub/c.txt'
		];

		$this->assertSame($expected, Dir::index($dir, true));
	}

	/**
	 * @covers ::index
	 */
	public function testIndexIgnore()
	{
		Dir::$ignore = ['z.txt'];

		Dir::make($dir = static::TMP);
		Dir::make(static::TMP . '/sub');
		Dir::make(static::TMP . '/sub/sub');

		F::write(static::TMP . '/a.txt', 'test');
		F::write(static::TMP . '/d.txt', 'test');
		F::write(static::TMP . '/z.txt', 'test');
		F::write(static::TMP . '/sub/a.txt', 'test');
		F::write(static::TMP . '/sub/b.txt', 'test');
		F::write(static::TMP . '/sub/sub/a.txt', 'test');
		F::write(static::TMP . '/sub/sub/c.txt', 'test');

		// only global static $ignore
		$this->assertSame([
			'a.txt',
			'd.txt',
			'sub',
			'sub/a.txt',
			'sub/b.txt',
			'sub/sub',
			'sub/sub/a.txt',
			'sub/sub/c.txt'
		], Dir::index($dir, true));

		// disabling global static $ignore
		$this->assertSame([
			'a.txt',
			'd.txt',
			'sub',
			'sub/a.txt',
			'sub/b.txt',
			'sub/sub',
			'sub/sub/a.txt',
			'sub/sub/c.txt',
			'z.txt',
		], Dir::index($dir, true, false));

		// passing $ignore values
		$this->assertSame([
			'd.txt',
			'sub',
			'sub/b.txt',
			'sub/sub',
			'sub/sub/c.txt'
		], Dir::index($dir, true, [
			static::TMP . '/a.txt',
			static::TMP . '/sub/a.txt',
			static::TMP . '/sub/sub/a.txt'
		]));
	}

	/**
	 * @covers ::isWritable
	 */
	public function testIsWritable()
	{
		Dir::make(static::TMP);

		$this->assertSame(is_writable(static::TMP), Dir::isWritable(static::TMP));
	}

	/**
	 * @covers ::inventory
	 */
	public function testInventory()
	{
		$inventory = $this->create([
			'1_project-a',
			'2_project-b',
			'cover.jpg',
			'cover.jpg.txt',
			'projects.txt',
			'_ignore.txt',
			'.invisible'
		]);

		$this->assertSame('project-a', $inventory['children'][0]['slug']);
		$this->assertSame(1, $inventory['children'][0]['num']);

		$this->assertSame('project-b', $inventory['children'][1]['slug']);
		$this->assertSame(2, $inventory['children'][1]['num']);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('jpg', $inventory['files']['cover.jpg']['extension']);
		$this->assertArrayNotHasKey('_ignore.txt', $inventory['files']);
		$this->assertArrayNotHasKey('.invisible', $inventory['files']);

		$this->assertSame('projects', $inventory['template']);
	}

	/**
	 * @covers ::inventory
	 */
	public function testInventoryWithSkippedFiles()
	{
		$inventory = $this->create([
			'valid.jpg',
			'skipped.html',
			'skipped.htm',
			'skipped.php'
		]);

		$expected = [
			'valid.jpg'
		];

		$this->assertSame($expected, A::pluck($inventory['files'], 'filename'));
	}

	/**
	 * @covers ::inventory
	 */
	public function testInventoryChildSorting()
	{
		$inventory = $this->create([
			'1_project-c',
			'10_project-b',
			'11_project-a',
		]);

		$this->assertSame('project-c', $inventory['children'][0]['slug']);
		$this->assertSame('project-b', $inventory['children'][1]['slug']);
		$this->assertSame('project-a', $inventory['children'][2]['slug']);
	}

	/**
	 * @covers ::inventory
	 */
	public function testInventoryChildWithLeadingZero()
	{
		$inventory = $this->create([
			'01_project-c',
			'02_project-b',
			'03_project-a',
		]);

		$this->assertSame('project-c', $inventory['children'][0]['slug']);
		$this->assertSame(1, $inventory['children'][0]['num']);

		$this->assertSame('project-b', $inventory['children'][1]['slug']);
		$this->assertSame(2, $inventory['children'][1]['num']);

		$this->assertSame('project-a', $inventory['children'][2]['slug']);
		$this->assertSame(3, $inventory['children'][2]['num']);
	}

	/**
	 * @covers ::inventory
	 */
	public function testInventoryFileSorting()
	{
		$inventory = $this->create([
			'1-c.jpg',
			'10-b.jpg',
			'11-a.jpg',
		]);

		$files = array_values($inventory['files']);

		$this->assertSame('1-c.jpg', $files[0]['filename']);
		$this->assertSame('10-b.jpg', $files[1]['filename']);
		$this->assertSame('11-a.jpg', $files[2]['filename']);
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryTemplate
	 */
	public function testInventoryMissingTemplate()
	{
		$inventory = $this->create([
			'cover.jpg',
			'cover.jpg.txt'
		]);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('default', $inventory['template']);
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryTemplate
	 */
	public function testInventoryTemplateWithDotInFilename()
	{
		$inventory = $this->create([
			'cover.jpg',
			'cover.jpg.txt',
			'article.video.txt'
		]);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('article.video', $inventory['template']);
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryTemplate
	 */
	public function testInventoryExtension()
	{
		$inventory = $this->create([
			'cover.jpg',
			'cover.jpg.md',
			'article.md'
		], 'md');

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('article', $inventory['template']);
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryTemplate
	 */
	public function testInventoryIgnore()
	{
		$inventory = $this->create([
			'cover.jpg',
			'article.txt'
		], 'txt', ['cover.jpg']);

		$this->assertCount(0, $inventory['files']);
		$this->assertSame('article', $inventory['template']);
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryTemplate
	 */
	public function testInventoryMultilang()
	{
		$inventory = $this->create([
			'cover.jpg',
			'cover.jpg.en.txt',
			'article.en.txt',
			'article.de.txt'
		], 'txt', null, true);

		$this->assertSame('cover.jpg', $inventory['files']['cover.jpg']['filename']);
		$this->assertSame('article', $inventory['template']);
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryChild
	 */
	public function testInventoryChildModels()
	{
		Page::$models = [
			'a' => 'A',
			'b' => 'A'
		];

		$inventory = $this->create([
			'child-with-model-a/a.txt',
			'child-with-model-b/b.txt',
			'child-without-model-c/c.txt'
		]);

		$this->assertSame('a', $inventory['children'][0]['model']);
		$this->assertSame('b', $inventory['children'][1]['model']);
		$this->assertNull($inventory['children'][2]['model']);

		Page::$models = [];
	}

	/**
	 * @covers ::inventory
	 * @covers ::inventoryChild
	 */
	public function testInventoryChildMultilangModels()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
				]
			]
		]);

		Page::$models = [
			'a' => 'A',
			'b' => 'A'
		];

		$inventory = $this->create([
			'child-with-model-a/a.de.txt',
			'child-with-model-a/a.en.txt',
			'child-with-model-b/b.de.txt',
			'child-with-model-b/b.en.txt',
			'child-without-model-c/c.de.txt',
			'child-without-model-c/c.en.txt'
		], 'txt', null, true);

		$this->assertSame('a', $inventory['children'][0]['model']);
		$this->assertSame('b', $inventory['children'][1]['model']);
		$this->assertNull($inventory['children'][2]['model']);

		Page::$models = [];
	}

	/**
	 * @covers ::make
	 */
	public function testMake()
	{
		$this->assertTrue(Dir::make(static::TMP));
		$this->assertFalse(Dir::make(''));
	}

	/**
	 * @covers ::make
	 */
	public function testMakeFileExists()
	{
		$test = static::TMP . '/test';

		$this->expectException('Exception');
		$this->expectExceptionMessage('A file with the name "' . $test . '" already exists');

		F::write($test, '');
		Dir::make($test);
	}

	/**
	 * @covers ::modified
	 */
	public function testModified()
	{
		Dir::make(static::TMP);

		$this->assertTrue(is_int(Dir::modified(static::TMP)));
	}

	/**
	 * @covers ::move
	 */
	public function testMove()
	{
		Dir::make(static::TMP . '/1');

		$this->assertTrue(Dir::move(static::TMP . '/1', static::TMP . '/2'));
	}

	/**
	 * @covers ::move
	 */
	public function testMoveNonExisting()
	{
		$this->assertFalse(Dir::move('/does-not-exist', static::TMP . '/2'));
	}

	/**
	 * @covers ::link
	 */
	public function testLink()
	{
		$source = static::TMP . '/source';
		$link   = static::TMP . '/link';

		Dir::make($source);

		$this->assertTrue(Dir::link($source, $link));
		$this->assertTrue(is_link($link));
	}

	/**
	 * @covers ::link
	 */
	public function testLinkExistingLink()
	{
		$source = static::TMP . '/source';
		$link   = static::TMP . '/link';

		Dir::make($source);
		Dir::link($source, $link);

		$this->assertTrue(Dir::link($source, $link));
	}

	/**
	 * @covers ::link
	 */
	public function testLinkWithoutSource()
	{
		$source = static::TMP . '/source';
		$link   = static::TMP . '/link';

		$this->expectExceptionMessage('Expection');
		$this->expectExceptionMessage('The directory "' . $source . '" does not exist and cannot be linked');

		Dir::link($source, $link);
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		Dir::make(static::TMP);

		touch(static::TMP . '/a.jpg');
		touch(static::TMP . '/b.jpg');
		touch(static::TMP . '/c.jpg');

		// relative
		$files    = Dir::read(static::TMP);
		$expected = [
			'a.jpg',
			'b.jpg',
			'c.jpg'
		];

		$this->assertSame($expected, $files);

		// absolute
		$files    = Dir::read(static::TMP, null, true);
		$expected = [
			static::TMP . '/a.jpg',
			static::TMP . '/b.jpg',
			static::TMP . '/c.jpg'
		];

		$this->assertSame($expected, $files);

		// ignore
		$files    = Dir::read(static::TMP, ['a.jpg']);
		$expected = [
			'b.jpg',
			'c.jpg'
		];

		$this->assertSame($expected, $files);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpath()
	{
		$path = Dir::realpath(__DIR__ . '/../Filesystem');
		$this->assertSame(__DIR__, $path);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToMissingFile()
	{
		$path = __DIR__ . '/../does-not-exist';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The directory does not exist at the given path: "' . $path . '"');

		Dir::realpath($path);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToParent()
	{
		$parent = __DIR__ . '/..';
		$dir    = $parent . '/Filesystem';
		$path   = Dir::realpath($dir, $parent);

		$this->assertSame(__DIR__, $path);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToNonExistingParent()
	{
		$parent = __DIR__ . '/../does-not-exist';
		$dir    = __DIR__ . '/../Filesystem';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The parent directory does not exist: "' . $parent . '"');

		Dir::realpath($dir, $parent);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToInvalidParent()
	{
		$parent = __DIR__ . '/../Cms';
		$dir    = __DIR__ . '/../Filesystem';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The directory is not within the parent directory');

		Dir::realpath($dir, $parent);
	}

	/**
	 * @covers ::remove
	 */
	public function testRemove()
	{
		Dir::make(static::TMP);

		$this->assertDirectoryExists(static::TMP);
		$this->assertTrue(Dir::remove(static::TMP));
		$this->assertDirectoryDoesNotExist(static::TMP);
	}

	/**
	 * @covers ::isReadable
	 */
	public function testIsReadable()
	{
		Dir::make(static::TMP);

		$this->assertSame(is_readable(static::TMP), Dir::isReadable(static::TMP));
	}

	/**
	 * @covers ::dirs
	 * @covers ::files
	 */
	public function testReadDirsAndFiles()
	{
		Dir::make(static::TMP);
		Dir::make(static::TMP . '/a');
		Dir::make(static::TMP . '/b');
		Dir::make(static::TMP . '/c');

		touch(static::TMP . '/a.txt');
		touch(static::TMP . '/b.jpg');
		touch(static::TMP . '/c.doc');

		$any = Dir::read(static::TMP);
		$expected = ['a', 'a.txt', 'b', 'b.jpg', 'c', 'c.doc'];

		$this->assertSame($any, $expected);

		// relative dirs
		$dirs = Dir::dirs(static::TMP);
		$expected = ['a', 'b', 'c'];

		$this->assertSame($expected, $dirs);

		// absolute dirs
		$dirs = Dir::dirs(static::TMP, null, true);
		$expected = [
			static::TMP . '/a',
			static::TMP . '/b',
			static::TMP . '/c'
		];

		$this->assertSame($expected, $dirs);

		// relative files
		$files = Dir::files(static::TMP);
		$expected = ['a.txt', 'b.jpg', 'c.doc'];

		$this->assertSame($expected, $files);

		// absolute files
		$files = Dir::files(static::TMP, null, true);
		$expected = [
			static::TMP . '/a.txt',
			static::TMP . '/b.jpg',
			static::TMP . '/c.doc'
		];

		$this->assertSame($expected, $files);
	}

	/**
	 * @covers ::size
	 * @covers ::niceSize
	 */
	public function testSize()
	{
		Dir::make(static::TMP);

		F::write(static::TMP . '/testfile-1.txt', Str::random(5));
		F::write(static::TMP . '/testfile-2.txt', Str::random(5));
		F::write(static::TMP . '/testfile-3.txt', Str::random(5));

		$this->assertSame(15, Dir::size(static::TMP));
		$this->assertSame(15, Dir::size(static::TMP, false));
		$this->assertSame('15 B', Dir::niceSize(static::TMP));

		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::size
	 */
	public function testSizeWithNestedFolders()
	{
		Dir::make(static::TMP);
		Dir::make(static::TMP . '/sub');
		Dir::make(static::TMP . '/sub/sub');

		F::write(static::TMP . '/testfile-1.txt', Str::random(5));
		F::write(static::TMP . '/sub/testfile-2.txt', Str::random(5));
		F::write(static::TMP . '/sub/sub/testfile-3.txt', Str::random(5));

		$this->assertSame(15, Dir::size(static::TMP));
		$this->assertSame(5, Dir::size(static::TMP, false));
		$this->assertSame('15 B', Dir::niceSize(static::TMP));

		Dir::remove(static::TMP);
	}

	/**
	 * @covers ::size
	 */
	public function testSizeOfNonExistingDir()
	{
		$this->assertFalse(Dir::size('/does-not-exist'));
	}

	/**
	 * @covers ::wasModifiedAfter
	 */
	public function testWasModifiedAfter()
	{
		$time = time();

		Dir::make(static::TMP);
		Dir::make(static::TMP . '/sub');
		F::write(static::TMP . '/sub/test.txt', 'foo');

		// the modification time of the folder is already later
		// than the given time
		$this->assertTrue(Dir::wasModifiedAfter(static::TMP, $time - 10));

		// ensure that the modified times are consistent
		// to make the test more reliable
		touch(static::TMP, $time);
		touch(static::TMP . '/sub', $time);
		touch(static::TMP . '/sub/test.txt', $time);

		$this->assertFalse(Dir::wasModifiedAfter(static::TMP, $time));

		touch(static::TMP . '/sub/test.txt', $time + 1);

		$this->assertTrue(Dir::wasModifiedAfter(static::TMP, $time));

		touch(static::TMP . '/sub', $time + 1);
		touch(static::TMP . '/sub/test.txt', $time);

		$this->assertTrue(Dir::wasModifiedAfter(static::TMP, $time));

		// sanity check
		touch(static::TMP . '/sub', $time);

		$this->assertFalse(Dir::wasModifiedAfter(static::TMP, $time));
	}
}
