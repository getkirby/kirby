<?php

namespace Kirby\Toolkit;

class DirTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures/dir';

    protected $tmp;
    protected $moved;

    protected function setUp(): void
    {
        $this->tmp   = static::FIXTURES . '/test';
        $this->moved = static::FIXTURES . '/moved';

        Dir::remove($this->tmp);
        Dir::remove($this->moved);
    }

    public function testCopy()
    {
        $src    = static::FIXTURES . '/copy';
        $target = static::FIXTURES . '/copy-target';

        $result = Dir::copy($src, $target);

        $this->assertTrue($result);

        $this->assertTrue(file_exists($target . '/a.txt'));
        $this->assertTrue(file_exists($target . '/subfolder/b.txt'));

        // clean up
        Dir::remove($target);
    }

    public function testCopyMissingSource()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The directory "/does-not-exist" does not exist');

        $src    = '/does-not-exist';
        $target = static::FIXTURES . '/copy-target';

        $result = Dir::copy($src, $target);
    }

    public function testCopyExistingTarget()
    {
        $src    = static::FIXTURES . '/copy';
        $target = static::FIXTURES . '/copy';

        $this->expectException('Exception');
        $this->expectExceptionMessage('The target directory "' . $target . '" exists');

        $result = Dir::copy($src, $target);
    }

    public function testCopyExists()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The target directory');

        $src    = static::FIXTURES . '/copy';
        $target = static::FIXTURES . '/copy';

        Dir::copy($src, $target);
    }

    public function testExists()
    {
        $this->assertFalse(Dir::exists($this->tmp));
        Dir::make($this->tmp);
        $this->assertTrue(Dir::exists($this->tmp));
    }

    public function testIndex()
    {
        Dir::make($dir = $this->tmp);
        Dir::make($sub = $this->tmp . '/sub');

        F::write($a = $this->tmp . '/a.txt', 'test');
        F::write($b = $this->tmp . '/b.txt', 'test');

        $expected = [
            'a.txt',
            'b.txt',
            'sub',
        ];

        $this->assertEquals($expected, Dir::index($dir));
    }

    public function testIndexRecursive()
    {
        Dir::make($dir = $this->tmp);
        Dir::make($sub = $this->tmp . '/sub');
        Dir::make($subsub = $this->tmp . '/sub/sub');

        F::write($a = $this->tmp . '/a.txt', 'test');
        F::write($b = $this->tmp . '/sub/b.txt', 'test');
        F::write($c = $this->tmp . '/sub/sub/c.txt', 'test');

        $expected = [
            'a.txt',
            'sub',
            'sub/b.txt',
            'sub/sub',
            'sub/sub/c.txt'
        ];

        $this->assertEquals($expected, Dir::index($dir, true));
    }

    public function testIsWritable()
    {
        Dir::make($this->tmp);

        $this->assertEquals(is_writable($this->tmp), Dir::isWritable($this->tmp));
    }

    public function testMake()
    {
        $this->assertTrue(Dir::make($this->tmp));
        $this->assertFalse(Dir::make(''));
    }

    public function testModified()
    {
        Dir::make($this->tmp);

        $this->assertTrue(is_int(Dir::modified($this->tmp)));
    }

    public function testMove()
    {
        Dir::make($this->tmp);

        $this->assertTrue(Dir::move($this->tmp, $this->moved));
    }

    public function testMoveNonExisting()
    {
        $this->assertFalse(Dir::move('/does-not-exist', $this->moved));
    }

    public function testLink()
    {
        $source = $this->tmp . '/source';
        $link   = $this->tmp . '/link';

        Dir::make($source);

        $this->assertTrue(Dir::link($source, $link));
        $this->assertTrue(is_link($link));
    }

    public function testLinkExistingLink()
    {
        $source = $this->tmp . '/source';
        $link   = $this->tmp . '/link';

        Dir::make($source);
        Dir::link($source, $link);

        $this->assertTrue(Dir::link($source, $link));
    }

    public function testLinkWithoutSource()
    {
        $source = $this->tmp . '/source';
        $link   = $this->tmp . '/link';

        $this->expectExceptionMessage('Expection');
        $this->expectExceptionMessage('The directory "' . $source . '" does not exist and cannot be linked');

        Dir::link($source, $link);
    }

    public function testRead()
    {
        Dir::make($this->tmp);

        touch($this->tmp . '/a.jpg');
        touch($this->tmp . '/b.jpg');
        touch($this->tmp . '/c.jpg');

        // relative
        $files    = Dir::read($this->tmp);
        $expected = [
            'a.jpg',
            'b.jpg',
            'c.jpg'
        ];

        $this->assertEquals($expected, $files);

        // absolute
        $files    = Dir::read($this->tmp, null, true);
        $expected = [
            $this->tmp . '/a.jpg',
            $this->tmp . '/b.jpg',
            $this->tmp . '/c.jpg'
        ];

        $this->assertEquals($expected, $files);

        // ignore
        $files    = Dir::read($this->tmp, ['a.jpg']);
        $expected = [
            'b.jpg',
            'c.jpg'
        ];

        $this->assertEquals($expected, $files);
    }

    public function testRemove()
    {
        Dir::make($this->tmp);

        $this->assertTrue(is_dir($this->tmp));
        $this->assertTrue(Dir::remove($this->tmp));
        $this->assertFalse(is_dir($this->tmp));
    }

    public function testReadable()
    {
        Dir::make($this->tmp);

        $this->assertEquals(is_readable($this->tmp), Dir::isReadable($this->tmp));
    }

    public function testReadDirsAndFiles()
    {
        Dir::make($root = static::FIXTURES . '/dirs');
        Dir::make($root . '/a');
        Dir::make($root . '/b');
        Dir::make($root . '/c');

        touch($root . '/a.txt');
        touch($root . '/b.jpg');
        touch($root . '/c.doc');

        $any = Dir::read($root);
        $expected = ['a', 'a.txt', 'b', 'b.jpg', 'c', 'c.doc'];

        $this->assertEquals($any, $expected);

        // relative dirs
        $dirs = Dir::dirs($root);
        $expected = ['a', 'b', 'c'];

        $this->assertEquals($expected, $dirs);

        // absolute dirs
        $dirs = Dir::dirs($root, null, true);
        $expected = [
            $root . '/a',
            $root . '/b',
            $root . '/c'
        ];

        $this->assertEquals($expected, $dirs);

        // relative files
        $files = Dir::files($root);
        $expected = ['a.txt', 'b.jpg', 'c.doc'];

        $this->assertEquals($expected, $files);

        // absolute files
        $files = Dir::files($root, null, true);
        $expected = [
            $root . '/a.txt',
            $root . '/b.jpg',
            $root . '/c.doc'
        ];

        $this->assertEquals($expected, $files);

        Dir::remove($root);
    }

    public function testSize()
    {
        Dir::make($this->tmp);

        F::write($this->tmp . '/testfile-1.txt', Str::random(5));
        F::write($this->tmp . '/testfile-2.txt', Str::random(5));
        F::write($this->tmp . '/testfile-3.txt', Str::random(5));

        $this->assertEquals(15, Dir::size($this->tmp));
        $this->assertEquals('15 B', Dir::niceSize($this->tmp));

        Dir::remove($this->tmp);
    }

    public function testSizeWithNestedFolders()
    {
        Dir::make($this->tmp);
        Dir::make($this->tmp . '/sub');
        Dir::make($this->tmp . '/sub/sub');

        F::write($this->tmp . '/testfile-1.txt', Str::random(5));
        F::write($this->tmp . '/sub/testfile-2.txt', Str::random(5));
        F::write($this->tmp . '/sub/sub/testfile-3.txt', Str::random(5));

        $this->assertEquals(15, Dir::size($this->tmp));
        $this->assertEquals('15 B', Dir::niceSize($this->tmp));

        Dir::remove($this->tmp);
    }

    public function testSizeOfNonExistingDir()
    {
        $this->assertFalse(Dir::size('/does-not-exist'));
    }

    public function testWasModifiedAfter()
    {
        $time = time();

        Dir::make($this->tmp);
        Dir::make($this->tmp . '/sub');
        F::write($this->tmp . '/sub/test.txt', 'foo');

        // ensure that the modified times are consistent
        // to make the test more reliable
        touch($this->tmp, $time);
        touch($this->tmp . '/sub', $time);
        touch($this->tmp . '/sub/test.txt', $time);

        $this->assertFalse(Dir::wasModifiedAfter($this->tmp, $time));

        touch($this->tmp . '/sub/test.txt', $time + 1);

        $this->assertTrue(Dir::wasModifiedAfter($this->tmp, $time));

        touch($this->tmp . '/sub', $time + 1);
        touch($this->tmp . '/sub/test.txt', $time);

        $this->assertTrue(Dir::wasModifiedAfter($this->tmp, $time));

        // sanity check
        touch($this->tmp . '/sub', $time);

        $this->assertFalse(Dir::wasModifiedAfter($this->tmp, $time));
    }
}
