<?php

namespace Kirby\Filesystem;

use Exception;
use Kirby\Exception\LogicException;
use Kirby\Http\HeadersSent;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * @coversDefaultClass \Kirby\Filesystem\F
 */
class FTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/f';
	public const TMP      = KIRBY_TMP_DIR . '/Filesystem.F';

	protected bool $hasErrorHandler = false;
	protected string $sample;
	protected string $test;

	public function setUp(): void
	{
		$this->sample = static::FIXTURES . '/test.txt';
		$this->test   = static::TMP . '/moved.txt';

		Dir::remove(static::TMP);
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);

		if ($this->hasErrorHandler === true) {
			restore_error_handler();
			$this->hasErrorHandler = false;
		}

		HeadersSent::$value = false;
	}

	/**
	 * @covers ::append
	 */
	public function testAppend()
	{
		F::copy($this->sample, $this->test);
		$this->assertTrue(F::append($this->test, ' is awesome'));
	}

	/**
	 * @covers ::base64
	 */
	public function testBase64()
	{
		F::write($this->test, 'test');
		$expected = base64_encode('test');

		$this->assertSame($expected, F::base64($this->test));
	}

	/**
	 * @covers ::copy
	 */
	public function testCopy()
	{
		$origin = static::TMP . '/a.txt';
		F::write($origin, 'test');

		$this->assertFileDoesNotExist($this->test);
		$this->assertTrue(F::copy($origin, $this->test));
		$this->assertFileExists($this->test);
	}

	/**
	 * @covers ::dirname
	 */
	public function testDirname()
	{
		$this->assertSame(dirname($this->test), F::dirname($this->test));
	}

	/**
	 * @covers ::exists
	 */
	public function testExists()
	{
		$this->assertFalse(F::exists($this->test));
		touch($this->test);
		$this->assertTrue(F::exists($this->test));
	}

	/**
	 * @covers ::exists
	 */
	public function testExistsIn()
	{
		$this->assertFalse(F::exists($this->test, static::TMP));
		touch($this->test);
		$this->assertTrue(F::exists($this->test, static::TMP));
		$this->assertTrue(F::exists(static::TMP . '/../Filesystem.F/moved.txt', static::TMP));
		$this->assertTrue(F::exists($this->test, dirname(static::TMP)));
		$this->assertFalse(F::exists($this->test, static::FIXTURES));
	}

	/**
	 * @covers ::extension
	 */
	public function testExtension()
	{
		$this->assertSame('php', F::extension(__FILE__));
		$this->assertSame('test.jpg', F::extension($this->sample, 'jpg'));
	}

	/**
	 * @covers ::extensionToType
	 */
	public function testExtensionToType()
	{
		$this->assertSame('image', F::extensionToType('jpg'));
		$this->assertFalse(F::extensionToType('something'));
	}

	/**
	 * @covers ::extensions
	 */
	public function testExtensions()
	{
		$this->assertSame(array_keys(Mime::types()), F::extensions());
		$this->assertSame(F::$types['image'], F::extensions('image'));
		$this->assertSame([], F::extensions('unknown-type'));
	}

	/**
	 * @covers ::filename
	 */
	public function testFilename()
	{
		$this->assertSame('test.txt', F::filename($this->sample));
	}

	/**
	 * @covers ::is
	 */
	public function testIs()
	{
		F::write($this->test, 'test');

		$this->assertTrue(F::is($this->test, 'txt'));
		$this->assertTrue(F::is($this->test, 'text/plain'));
		$this->assertFalse(F::is($this->test, 'something/weird'));
		$this->assertFalse(F::is($this->test, 'no-clue'));
	}

	/**
	 * @covers ::isReadable
	 */
	public function testIsReadable()
	{
		F::write($this->test, 'test');
		$this->assertSame(is_readable($this->test), F::isReadable($this->test));
	}

	/**
	 * @covers ::isWritable
	 */
	public function testIsWritable()
	{
		F::write($this->test, 'test');
		$this->assertSame(is_writable($this->test), F::isWritable($this->test));
	}

	/**
	 * @covers ::link
	 */
	public function testLink()
	{
		$src  = static::TMP . '/a.txt';
		$link = static::TMP . '/b.txt';

		F::write($src, 'test');

		$this->assertTrue(F::link($src, $link));
		$this->assertFileExists($link);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpath()
	{
		$path = F::realpath(__DIR__ . '/../Filesystem/FTest.php');
		$this->assertSame(__FILE__, $path);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToMissingFile()
	{
		$path = __DIR__ . '/../does-not-exist.php';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file does not exist at the given path: "' . $path . '"');

		F::realpath($path);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToParent()
	{
		$parent = __DIR__ . '/..';
		$file   = $parent . '/Filesystem/FTest.php';
		$path   = F::realpath($file, $parent);

		$this->assertSame(__FILE__, $path);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToNonExistingParent()
	{
		$parent = __DIR__ . '/../does-not-exist';
		$file   = __DIR__ . '/../Filesystem/FTest.php';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The parent directory does not exist: "' . $parent . '"');

		F::realpath($file, $parent);
	}

	/**
	 * @covers ::realpath
	 */
	public function testRealpathToInvalidParent()
	{
		$parent = __DIR__ . '/../Cms';
		$file   = __DIR__ . '/../Filesystem/FTest.php';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file is not within the parent directory');

		F::realpath($file, $parent);
	}

	/**
	 * @covers ::relativepath
	 */
	public function testRelativePath()
	{
		$path = F::relativepath(__FILE__, __DIR__);
		$this->assertSame('/' . basename(__FILE__), $path);

		$path = F::relativepath(__FILE__, __DIR__ . '/');
		$this->assertSame('/' . basename(__FILE__), $path);
	}

	/**
	 * @covers ::relativepath
	 */
	public function testRelativePathWithEmptyBase()
	{
		$path = F::relativepath(__FILE__, '');
		$this->assertSame(basename(__FILE__), $path);

		$path = F::relativepath(__FILE__, null);
		$this->assertSame(basename(__FILE__), $path);
	}

	/**
	 * @covers ::relativepath
	 */
	public function testRelativePathWithUnrelatedBase()
	{
		$path = F::relativepath(__DIR__ . '/fruits/apples/fuji.txt', __DIR__ . '/fruits/pineapples');
		$this->assertSame('../apples/fuji.txt', $path);

		$path = F::relativepath(__DIR__ . '/fruits/apples/gala.txt', __DIR__ . '/vegetables');
		$this->assertSame('../fruits/apples/gala.txt', $path);

		$path = F::relativepath(__DIR__ . '/fruits/apples/granny-smith.txt', __DIR__ . '/vegetables/');
		$this->assertSame('../fruits/apples/granny-smith.txt', $path);

		$path = F::relativepath(__DIR__ . '/fruits/apples/', __DIR__ . '/vegetables/');
		$this->assertSame('../fruits/apples', $path);

		$path = F::relativepath(__DIR__ . '/fruits/oranges/', __DIR__ . '/vegetables');
		$this->assertSame('../fruits/oranges', $path);

		$path = F::relativepath(__DIR__ . '/fruits/apples/honeycrisp.txt', __DIR__ . '/vegetables/squash');
		$this->assertSame('../../fruits/apples/honeycrisp.txt', $path);

		$path = F::relativepath(__DIR__ . '/test.txt', __DIR__ . '/foo/bar/baz');
		$this->assertSame('../../../test.txt', $path);

		$path = F::relativepath('foo/path-extra/file.txt', 'foo/path');
		$this->assertSame('../path-extra/file.txt', $path);
	}

	/**
	 * @covers ::relativepath
	 */
	public function testRelativePathOnWindows()
	{
		$file = 'C:\xampp\htdocs\index.php';
		$in   = 'C:/xampp/htdocs';

		$path = F::relativepath($file, $in);
		$this->assertSame('/index.php', $path);
	}

	/**
	 * @covers ::link
	 */
	public function testLinkSymlink()
	{
		$src  = static::TMP . '/a.txt';
		$link = static::TMP . '/b.txt';

		F::write($src, 'test');

		$this->assertTrue(F::link($src, $link, 'symlink'));
		$this->assertTrue(is_link($link));
	}

	/**
	 * @covers ::link
	 */
	public function testLinkExistingLink()
	{
		$src  = static::TMP . '/a.txt';
		$link = static::TMP . '/b.txt';

		F::write($src, 'test');
		F::link($src, $link);

		$this->assertTrue(F::link($src, $link));
	}

	/**
	 * @covers ::link
	 */
	public function testLinkWithMissingSource()
	{
		$src  = static::TMP . '/a.txt';
		$link = static::TMP . '/b.txt';

		$this->expectExceptionMessage('Expection');
		$this->expectExceptionMessage('The file "' . $src . '" does not exist and cannot be linked');

		F::link($src, $link);
	}

	/**
	 * @covers ::load
	 */
	public function testLoad()
	{
		// basic behavior
		F::write($file = static::TMP . '/test.php', '<?php return "foo";');
		$this->assertSame('foo', F::load($file));

		// non-existing file
		$this->assertSame('foo', F::load('does-not-exist.php', 'foo'));

		// type mismatch
		F::write($file = static::TMP . '/test.php', '<?php return "foo";');
		$expected = ['a' => 'b'];
		$this->assertSame($expected, F::load($file, $expected));

		// type mismatch with overwritten $fallback
		F::write($file = static::TMP . '/test.php', '<?php $fallback = "test"; return "foo";');
		$expected = ['a' => 'b'];
		$this->assertSame($expected, F::load($file, $expected));

		// with data
		F::write($file = static::TMP . '/test.php', '<?php return $variable;');
		$this->assertSame('foobar', F::load($file, null, ['variable' => 'foobar']));

		// with overwritten $data
		$this->assertSame('foobar', F::load($file, null, ['variable' => 'foobar', 'data' => []]));

		// with overwritten $file
		$this->assertSame('foobar', F::load($file, null, ['variable' => 'foobar', 'file' => null]));

		// protection against accidental output (without output)
		F::write($file = static::TMP . '/test.php', '<?php return "foo";');
		$this->assertSame('foo', F::load($file, allowOutput: false));

		// no protection against accidental output (with output)
		F::write($file = static::TMP . '/test.php', '<?php Kirby\Http\HeadersSent::$value = true; return "foo";');
		$this->assertSame('foo', F::load($file));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadAccidentalOutput()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Disallowed output from file file.php:123, possible accidental whitespace?');

		F::write($file = static::TMP . '/test.php', '<?php Kirby\Http\HeadersSent::$value = true; return "foo";');

		F::load($file, allowOutput: false);
	}

	/**
	 * @covers ::loadClasses
	 */
	public function testLoadClasses()
	{
		F::loadClasses([
			'ftest\\a' => static::FIXTURES . '/load/a/a.php',
			'ftest\\output' => static::FIXTURES . '/load/output.php'
		]);

		F::loadClasses([
			'FTest\\B' => 'B.php',
		], static::FIXTURES . '/load/B');

		$this->assertTrue(class_exists('FTest\\A'));
		$this->assertTrue(class_exists('FTest\\B'));
		$this->assertFalse(class_exists('FTest\\C'));

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Disallowed output from file file.php:123, possible accidental whitespace?');
		class_exists('FTest\\Output');
	}

	/**
	 * @covers ::loadOnce
	 */
	public function testLoadOnce()
	{
		// basic behavior
		F::write($file = static::TMP . '/test1.php', '<?php return "foo";');
		$this->assertTrue(F::loadOnce($file));

		// non-existing file
		$this->assertFalse(F::loadOnce('does-not-exist.php'));

		// protection against accidental output (without output)
		F::write($file = static::TMP . '/test2.php', '<?php return "foo";');
		$this->assertTrue(F::loadOnce($file, allowOutput: false));

		// no protection against accidental output (with output)
		F::write($file = static::TMP . '/test3.php', '<?php Kirby\Http\HeadersSent::$value = true; return "foo";');
		$this->assertTrue(F::loadOnce($file));
	}

	/**
	 * @covers ::loadOnce
	 */
	public function testLoadOnceAccidentalOutput()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Disallowed output from file file.php:123, possible accidental whitespace?');

		F::write($file = static::TMP . '/test4.php', '<?php Kirby\Http\HeadersSent::$value = true; return "foo";');

		F::loadOnce($file, allowOutput: false);
	}

	/**
	 * @covers ::move
	 */
	public function testMove()
	{
		F::write($origin = static::TMP . '/a.txt', 'test');

		// simply move file
		$this->assertFileDoesNotExist($this->test);
		$this->assertFileExists($origin);

		$this->assertTrue(F::move($origin, $this->test));

		$this->assertFileExists($this->test);
		$this->assertFileDoesNotExist($origin);
		$this->assertSame('test', file_get_contents($this->test));

		// replace file via moving
		F::copy($this->test, $origin);

		$this->assertFileExists($origin);
		$this->assertFileExists($this->test);

		$this->assertFalse(F::move($this->test, $origin));
		$this->assertTrue(F::move($this->test, $origin, true));

		$this->assertFileDoesNotExist($this->test);
		$this->assertFileExists($origin);
		$this->assertSame('test', file_get_contents($origin));
	}

	/**
	 * @covers ::move
	 */
	public function testMoveAcrossDevices()
	{
		// try to find a suitable path on a different device (filesystem)
		if (is_dir('/dev/shm') === true) {
			// use tmpfs mount point on GitHub Actions
			$tmpDir = '/dev/shm';
		} else {
			// no luck, try the system temp dir,
			// which often also uses tmpfs
			$tmpDir = sys_get_temp_dir();
		}

		if (stat(static::TMP)['dev'] === stat($tmpDir)['dev']) {
			$this->markTestSkipped('Temporary directory "' . $tmpDir . '" is on the same filesystem');
			return;
		}

		F::write($origin = static::TMP . '/a.txt', 'test');
		$this->test = $tmpDir . '/kirby-test-' . uniqid();

		// simply move file
		$this->assertFileDoesNotExist($this->test);
		$this->assertFileExists($origin);

		$this->assertTrue(F::move($origin, $this->test));

		$this->assertFileExists($this->test);
		$this->assertFileDoesNotExist($origin);
		$this->assertSame('test', file_get_contents($this->test));

		// replace file via moving
		F::copy($this->test, $origin);

		$this->assertFileExists($origin);
		$this->assertFileExists($this->test);

		$this->assertFalse(F::move($this->test, $origin));
		$this->assertTrue(F::move($this->test, $origin, true));

		$this->assertFileDoesNotExist($this->test);
		$this->assertFileExists($origin);
		$this->assertSame('test', file_get_contents($origin));
	}

	/**
	 * @covers ::mime
	 */
	public function testMime()
	{
		F::write($this->test, 'test');
		$this->assertSame('text/plain', F::mime($this->test));
	}

	/**
	 * @covers ::mimeToExtension
	 */
	public function testMimeToExtension()
	{
		$this->assertSame('jpg', F::mimeToExtension('image/jpeg'));
		$this->assertFalse(F::mimeToExtension('image/something'));
	}

	/**
	 * @covers ::mimeToType
	 */
	public function testMimeToType()
	{
		$this->assertSame('image', F::mimeToType('image/jpeg'));
		$this->assertFalse(F::mimeToType('image/something'));
	}

	/**
	 * @covers ::modified
	 */
	public function testModified()
	{
		F::write($this->test, 'test');
		$this->assertSame(filemtime($this->test), F::modified($this->test));
	}

	/**
	 * @covers ::name
	 */
	public function testName()
	{
		$this->assertSame('test', F::name($this->sample));
	}

	/**
	 * @covers ::niceSize
	 */
	public function testNiceSize()
	{
		$locale = I18n::$locale;

		F::write($a = static::TMP . '/a.txt', 'test');
		F::write($b = static::TMP . '/b.txt', 'test');

		// for file path
		$this->assertSame('4 B', F::niceSize($a));

		// for array of file paths
		$this->assertSame('8 B', F::niceSize([$a, $b]));

		// for int
		$this->assertSame('4 B', F::niceSize(4));
		$this->assertSame('4 KB', F::niceSize(4096));
		$this->assertSame('4 KB', F::niceSize(4100));
		$this->assertSame('4.1 KB', F::niceSize(4200));
		$this->assertSame('4 MB', F::niceSize(4194304));
		$this->assertSame('4.29 MB', F::niceSize(4500000));
		$this->assertSame('4 GB', F::niceSize(4294967296));

		// default locale
		I18n::$locale = 'de';
		$this->assertSame('4,29 MB', F::niceSize(4500000));

		// custom locale
		$this->assertSame('4.29 MB', F::niceSize(4500000, 'en_US'));
		$this->assertSame('4,29 MB', F::niceSize(4500000, 'fr_FR'));

		// disable locale formatting
		$this->assertSame('4.29 MB', F::niceSize(4500000, false));

		// reset locale
		I18n::$locale = $locale;
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		file_put_contents($this->test, $content = 'my content is awesome');

		$this->assertSame($content, F::read($this->test));
	}

	/**
	 * @covers ::read
	 */
	public function testReadInvalidFile()
	{
		$this->assertFalse(F::read('invalid file'));
	}

	/**
	 * @covers ::read
	 */
	public function testReadRemoteFile()
	{
		$this->assertFalse(F::read('https://example.com/some-file.jpg'));
	}

	/**
	 * @covers ::remove
	 */
	public function testRemove()
	{
		F::write($a = static::TMP . '/a.jpg', '');

		$this->assertFileExists($a);

		$this->assertTrue(F::remove($a));

		$this->assertFileDoesNotExist($a);
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveAlreadyRemoved()
	{
		$this->assertFileDoesNotExist($a = static::TMP . '/a.jpg');

		$this->assertTrue(F::remove($a));

		$this->assertFileDoesNotExist($a);
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveDirectory()
	{
		Dir::make($a = static::TMP . '/a');

		$this->assertDirectoryExists($a);

		$this->assertFalse(@F::remove($a));

		$this->assertDirectoryExists($a);
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveLink()
	{
		F::write($a = static::TMP . '/a.jpg', '');
		symlink($a, $b = static::TMP . '/b.jpg');

		$this->assertFileExists($a);
		$this->assertTrue(is_link($b));

		$this->assertTrue(F::remove($b));

		$this->assertFileDoesNotExist($a);
		$this->assertTrue(is_link($b));
	}

	/**
	 * @covers ::remove
	 */
	public function testRemoveGlob()
	{
		F::write($a = static::TMP . '/a.jpg', '');
		F::write($b = static::TMP . '/a.1234.jpg', '');
		F::write($c = static::TMP . '/a.3456.jpg', '');

		$this->assertFileExists($a);
		$this->assertFileExists($b);
		$this->assertFileExists($c);

		F::remove(static::TMP . '/a*.jpg');

		$this->assertFileDoesNotExist($a);
		$this->assertFileDoesNotExist($b);
		$this->assertFileDoesNotExist($c);
	}

	/**
	 * @covers ::rename
	 */
	public function testRename()
	{
		F::write($origin = static::TMP . '/a.txt', 'test');

		// simply rename file
		$this->assertFileDoesNotExist($this->test);
		$this->assertFileExists($origin);

		$this->assertSame($this->test, F::rename($origin, 'moved'));

		$this->assertFileExists($this->test);
		$this->assertFileDoesNotExist($origin);

		// rename file with same name

		$this->assertFileExists($this->test);
		$this->assertFileDoesNotExist($origin);

		$this->assertSame($this->test, F::rename($this->test, 'moved'));
		$this->assertSame($this->test, F::rename($this->test, 'moved', true));

		$this->assertFileExists($this->test);
		$this->assertFileDoesNotExist($origin);

		// replace file via renaming
		F::copy($this->test, $origin);

		$this->assertFileExists($this->test);
		$this->assertFileExists($origin);

		$this->assertFalse(F::rename($this->test, 'a'));
		$this->assertSame($origin, F::rename($this->test, 'a', true));

		$this->assertFileDoesNotExist($this->test);
		$this->assertFileExists($origin);
	}

	/**
	 * @covers ::safeName
	 */
	public function testSafeName()
	{
		// make sure no language rules are still set
		Str::$language = [];

		// with extension
		$this->assertSame('uber-genious.txt', F::safeName('über genious.txt'));

		// with unsafe extension
		$this->assertSame('uber-genious.taxt', F::safeName('über genious.täxt'));

		// without extension
		$this->assertSame('uber-genious', F::safeName('über genious'));

		// with leading and trailing dash
		$this->assertSame('super.jpg', F::safeName('-super.jpg-'));

		// with leading and trailing underscore
		$this->assertSame('super.jpg', F::safeName('_super.jpg_'));

		// with leading and trailing dot
		$this->assertSame('super.jpg', F::safeName('.super.jpg.'));

		// leave allowed characters untouched
		$this->assertSame('file.a@b_c-d.jpg', F::safeName('file.a@b_c-d.jpg'));
	}

	/**
	 * @covers ::safeBasename
	 */
	public function testSafeBasename()
	{
		// make sure no language rules are still set
		Str::$language = [];

		// with extension
		$this->assertSame('uber-genious', F::safeBasename('über genious.txt'));

		// without extension
		$this->assertSame('uber-genious', F::safeBasename('über genious'));
		$this->assertSame('uber', F::safeBasename('über.genious'));
		$this->assertSame('uber.genious', F::safeBasename('über.genious', false));

		// with leading dash
		$this->assertSame('super', F::safeBasename('-super.jpg'));
	}

	/**
	 * @covers ::safeExtension
	 */
	public function testSafeExtension()
	{
		// make sure no language rules are still set
		Str::$language = [];

		$this->assertSame('txt', F::safeExtension('über genious.txt'));
		$this->assertSame('taxt', F::safeExtension('über genious.täxt'));
		$this->assertSame('taxt', F::safeExtension('täxt', false));
		$this->assertSame('', F::safeExtension('über genious'));
		$this->assertSame('jpg', F::safeExtension('-super.jpg'));
	}

	/**
	 * @covers ::size
	 */
	public function testSize()
	{
		F::write($a = static::TMP . '/a.txt', 'test');
		F::write($b = static::TMP . '/b.txt', 'test');

		$this->assertSame(4, F::size($a));
		$this->assertSame(4, F::size($b));
		$this->assertSame(8, F::size([$a, $b]));
	}

	/**
	 * @covers ::type
	 */
	public function testType()
	{
		$this->assertSame('image', F::type('jpg'));
		$this->assertSame('document', F::type('pdf'));
		$this->assertSame('archive', F::type('zip'));
		$this->assertSame('code', F::type('css'));
		$this->assertSame('code', F::type('content.php'));
		$this->assertSame('code', F::type('py'));
		$this->assertSame('code', F::type('java'));
		$this->assertNull(F::type('foo'));
		$this->assertNull(F::type('tmp'));
	}

	/**
	 * @covers ::type
	 */
	public function testTypeWithoutExtension()
	{
		F::write($file = static::TMP . '/test', '<?php echo "foo"; ?>');

		$this->assertSame('text/x-php', F::mime($file));
		$this->assertSame('code', F::type($file));
	}

	/**
	 * @covers ::type
	 */
	public function testTypeWithTmpExtension()
	{
		F::write($file = static::TMP . '/test.tmp', '<?php echo "foo"; ?>');

		$this->assertSame('text/x-php', F::mime($file));
		$this->assertSame('code', F::type($file));
	}

	/**
	 * @covers ::typeToExtensions
	 */
	public function testTypeToExtensions()
	{
		$this->assertSame(F::$types['audio'], F::typeToExtensions('audio'));
		$this->assertSame(F::$types['document'], F::typeToExtensions('document'));
		$this->assertNull(F::typeToExtensions('invalid'));
	}

	/**
	 * @covers ::unlink
	 */
	public function testUnlink()
	{
		touch(static::TMP . '/file');
		symlink(static::TMP . '/file', static::TMP . '/link-exists');
		symlink(static::TMP . '/invalid', static::TMP . '/link-invalid');

		$this->assertTrue(F::unlink(static::TMP . '/file'));
		$this->assertTrue(F::unlink(static::TMP . '/link-exists'));
		$this->assertTrue(F::unlink(static::TMP . '/link-invalid'));

		$this->assertFileDoesNotExist(static::TMP . '/file');
		$this->assertFalse(is_link(static::TMP . '/link-exists'));
		$this->assertFalse(is_link(static::TMP . '/link-invalid'));
	}

	/**
	 * @covers ::unlink
	 */
	public function testUnlinkAlredyDeleted()
	{
		$this->assertTrue(F::unlink(static::TMP . '/does-not-exist'));
	}

	/**
	 * @covers ::unlink
	 */
	public function testUnlinkFolder()
	{
		$this->hasErrorHandler = true;

		$called = false;
		set_error_handler(function (int $errno, string $errstr) use (&$called) {
			$called = true;

			$this->assertSame(E_WARNING, $errno);

			$expectedPrefix = 'unlink(' . static::TMP . '/folder): ';
			$expected = [
				$expectedPrefix . 'Is a directory',
				$expectedPrefix . 'Operation not permitted'
			];

			$this->assertTrue(in_array($errstr, $expected, true));
		});

		mkdir(static::TMP . '/folder');

		$this->assertFalse(F::unlink(static::TMP . '/folder'));

		$this->assertTrue($called);
	}

	/**
	 * @covers ::uri
	 */
	public function testURI()
	{
		F::write($this->test, 'test');

		$expected = 'dGVzdA==';
		$this->assertSame($expected, F::base64($this->test));

		$expected = 'data:text/plain;base64,dGVzdA==';
		$this->assertSame($expected, F::uri($this->test));
	}

	/**
	 * @covers ::uri
	 */
	public function testUriOfNonExistingFile()
	{
		$this->assertFalse(F::uri('/does-not-exist'));
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$this->assertTrue(F::write($this->test, 'my content'));
	}

	/**
	 * @covers ::write
	 */
	public function testWriteArray()
	{
		$input = ['a' => 'a'];

		F::write($this->test, $input);

		$result = unserialize(F::read($this->test));
		$this->assertSame($input, $result);
	}

	/**
	 * @covers ::write
	 */
	public function testWriteObject()
	{
		$input = new \stdClass([
			'a' => 'b'
		]);

		F::write($this->test, $input);

		$result = unserialize(F::read($this->test));
		$this->assertEquals($input, $result); // cannot use strict assertion (serialization)
	}

	/**
	 * @covers ::similar
	 */
	public function testSimilar()
	{
		F::write($a = static::TMP . '/a.jpg', '');
		F::write($b = static::TMP . '/a.1234.jpg', '');
		F::write($c = static::TMP . '/a.3456.jpg', '');

		$expected = [
			$b,
			$c,
			$a
		];

		$this->assertSame($expected, F::similar($a));
	}
}
