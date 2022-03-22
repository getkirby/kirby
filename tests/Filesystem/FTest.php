<?php

namespace Kirby\Filesystem;

use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * @coversDefaultClass \Kirby\Filesystem\F
 */
class FTest extends TestCase
{
    protected $fixtures;
    protected $test;
    protected $tmp;

    public function setUp(): void
    {
        $this->fixtures = __DIR__ . '/fixtures/f';
        $this->sample   = $this->fixtures . '/test.txt';
        $this->tmp      = __DIR__ . '/tmp';
        $this->test    = $this->tmp . '/moved.txt';

        Dir::remove($this->tmp);
        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
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
        $origin = $this->tmp . '/a.txt';
        F::write($origin, 'test');

        $this->assertFalse(file_exists($this->test));
        $this->assertTrue(F::copy($origin, $this->test));
        $this->assertTrue(file_exists($this->test));
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
        touch($this->test);
        $this->assertTrue(F::exists($this->test));
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
        $src  = $this->tmp . '/a.txt';
        $link = $this->tmp . '/b.txt';

        F::write($src, 'test');

        $this->assertTrue(F::link($src, $link));
        $this->assertTrue(is_file($link));
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

        $this->expectException('Exception');
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

        $this->expectException('Exception');
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

        $this->expectException('Exception');
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
        $src  = $this->tmp . '/a.txt';
        $link = $this->tmp . '/b.txt';

        F::write($src, 'test');

        $this->assertTrue(F::link($src, $link, 'symlink'));
        $this->assertTrue(is_link($link));
    }

    /**
     * @covers ::link
     */
    public function testLinkExistingLink()
    {
        $src  = $this->tmp . '/a.txt';
        $link = $this->tmp . '/b.txt';

        F::write($src, 'test');
        F::link($src, $link);

        $this->assertTrue(F::link($src, $link));
    }

    /**
     * @covers ::link
     */
    public function testLinkWithMissingSource()
    {
        $src  = $this->tmp . '/a.txt';
        $link = $this->tmp . '/b.txt';

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
        F::write($file = $this->tmp . '/test.php', '<?php return "foo";');
        $this->assertSame('foo', F::load($file));

        // non-existing file
        $this->assertSame('foo', F::load('does-not-exist.php', 'foo'));

        // type mismatch
        F::write($file = $this->tmp . '/test.php', '<?php return "foo";');
        $expected = ['a' => 'b'];
        $this->assertSame($expected, F::load($file, $expected));

        // type mismatch with overwritten $fallback
        F::write($file = $this->tmp . '/test.php', '<?php $fallback = "test"; return "foo";');
        $expected = ['a' => 'b'];
        $this->assertSame($expected, F::load($file, $expected));

        // with data
        F::write($file = $this->tmp . '/test.php', '<?php return $variable;');
        $this->assertSame('foobar', F::load($file, null, ['variable' => 'foobar']));

        // with overwritten $data
        $this->assertSame('foobar', F::load($file, null, ['variable' => 'foobar', 'data' => []]));

        // with overwritten $file
        $this->assertSame('foobar', F::load($file, null, ['variable' => 'foobar', 'file' => null]));
    }

    /**
     * @covers ::loadOnce
     */
    public function testLoadOnce()
    {
        // basic behavior
        F::write($file = $this->tmp . '/test.php', '<?php return "foo";');
        $this->assertTrue(F::loadOnce($file));

        // non-existing file
        $this->assertFalse(F::loadOnce('does-not-exist.php'));
    }

    /**
     * @covers ::move
     */
    public function testMove()
    {
        F::write($origin = $this->tmp . '/a.txt', 'test');

        // simply move file
        $this->assertFalse(file_exists($this->test));
        $this->assertTrue(file_exists($origin));

        $this->assertTrue(F::move($origin, $this->test));

        $this->assertTrue(file_exists($this->test));
        $this->assertFalse(file_exists($origin));

        // replace file via moving
        F::copy($this->test, $origin);

        $this->assertTrue(file_exists($origin));
        $this->assertTrue(file_exists($this->test));

        $this->assertFalse(F::move($this->test, $origin));
        $this->assertTrue(F::move($this->test, $origin, true));

        $this->assertFalse(file_exists($this->test));
        $this->assertTrue(file_exists($origin));
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
        $this->assertSame(false, F::mimeToExtension('image/something'));
    }

    /**
     * @covers ::mimeToType
     */
    public function testMimeToType()
    {
        $this->assertSame('image', F::mimeToType('image/jpeg'));
        $this->assertSame(false, F::mimeToType('image/something'));
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

        F::write($a = $this->tmp . '/a.txt', 'test');
        F::write($b = $this->tmp . '/b.txt', 'test');

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
        $this->assertFalse(F::read('invalid file'));

        // TODO: This test is unreliable in CI (does not always get a response)
        // $this->assertStringContainsString('Example Domain', F::read('https://example.com'));
    }

    /**
     * @covers ::remove
     */
    public function testRemove()
    {
        F::write($a = $this->tmp . '/a.jpg', '');

        $this->assertFileExists($a);

        F::remove($this->tmp . '/a.jpg');

        $this->assertFileDoesNotExist($a);
    }

    /**
     * @covers ::remove
     */
    public function testRemoveGlob()
    {
        F::write($a = $this->tmp . '/a.jpg', '');
        F::write($b = $this->tmp . '/a.1234.jpg', '');
        F::write($c = $this->tmp . '/a.3456.jpg', '');

        $this->assertFileExists($a);
        $this->assertFileExists($b);
        $this->assertFileExists($c);

        F::remove($this->tmp . '/a*.jpg');

        $this->assertFileDoesNotExist($a);
        $this->assertFileDoesNotExist($b);
        $this->assertFileDoesNotExist($c);
    }

    /**
     * @covers ::rename
     */
    public function testRename()
    {
        F::write($origin = $this->tmp . '/a.txt', 'test');

        // simply rename file
        $this->assertFalse(file_exists($this->test));
        $this->assertTrue(file_exists($origin));

        $this->assertSame($this->test, F::rename($origin, 'moved'));

        $this->assertTrue(file_exists($this->test));
        $this->assertFalse(file_exists($origin));

        // rename file with same name

        $this->assertTrue(file_exists($this->test));
        $this->assertFalse(file_exists($origin));

        $this->assertSame($this->test, F::rename($this->test, 'moved'));
        $this->assertSame($this->test, F::rename($this->test, 'moved', true));

        $this->assertTrue(file_exists($this->test));
        $this->assertFalse(file_exists($origin));

        // replace file via renaming
        F::copy($this->test, $origin);

        $this->assertTrue(file_exists($this->test));
        $this->assertTrue(file_exists($origin));

        $this->assertFalse(F::rename($this->test, 'a'));
        $this->assertSame($origin, F::rename($this->test, 'a', true));

        $this->assertFalse(file_exists($this->test));
        $this->assertTrue(file_exists($origin));
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

        // with leading dash
        $this->assertSame('super.jpg', F::safeName('-super.jpg'));

        // with leading underscore
        $this->assertSame('super.jpg', F::safeName('_super.jpg'));

        // with leading dot
        $this->assertSame('super.jpg', F::safeName('.super.jpg'));
    }

    /**
     * @covers ::size
     */
    public function testSize()
    {
        F::write($a = $this->tmp . '/a.txt', 'test');
        F::write($b = $this->tmp . '/b.txt', 'test');

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
    }

    /**
     * @covers ::type
     */
    public function testTypeWithoutExtension()
    {
        F::write($file = $this->tmp . '/test', '<?php echo "foo"; ?>');

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
        $input = new \stdClass();

        F::write($this->test, $input);

        $result = unserialize(F::read($this->test));
        $this->assertEquals($input, $result);
    }

    /**
     * @covers ::similar
     */
    public function testSimilar()
    {
        F::write($a = $this->tmp . '/a.jpg', '');
        F::write($b = $this->tmp . '/a.1234.jpg', '');
        F::write($c = $this->tmp . '/a.3456.jpg', '');

        $expected = [
            $b,
            $c,
            $a
        ];

        $this->assertSame($expected, F::similar($a));
    }
}
