<?php

namespace Kirby\Toolkit;

class DirTest extends TestCase
{

    const FIXTURES = __DIR__ . '/fixtures/dir';

    protected $tmp;
    protected $moved;

    protected function setUp()
    {
        $this->tmp   = static::FIXTURES . '/test';
        $this->moved = static::FIXTURES . '/moved';

        Dir::remove($this->tmp);
        Dir::remove($this->moved);
    }

    public function testMake()
    {
        $this->assertTrue(Dir::make($this->tmp));
    }

    public function testMove()
    {
        Dir::make($this->tmp);

        $this->assertTrue(Dir::move($this->tmp, $this->moved));
    }

    public function testRead()
    {
        Dir::make($this->tmp);

        touch($this->tmp . '/a.jpg');
        touch($this->tmp . '/b.jpg');
        touch($this->tmp . '/c.jpg');

        $files = Dir::read($this->tmp);
        $this->assertEquals(3, count($files));
    }

    public function testRemove()
    {
        Dir::make($this->tmp);

        $this->assertTrue(is_dir($this->tmp));
        $this->assertTrue(Dir::remove($this->tmp));
        $this->assertFalse(is_dir($this->tmp));
    }

    public function testSize()
    {
        Dir::make($this->tmp);

        F::write($this->tmp . '/testfile-1.txt', Str::random(5));
        F::write($this->tmp . '/testfile-2.txt', Str::random(5));
        F::write($this->tmp . '/testfile-3.txt', Str::random(5));

        $this->assertEquals(15, Dir::size($this->tmp));
        $this->assertEquals('15 B', Dir::niceSize($this->tmp));

        Dir::remove($this->tmp);
    }

    public function testModified()
    {
        Dir::make($this->tmp);

        $this->assertTrue(is_int(Dir::modified($this->tmp)));
    }

    public function testIsWritable()
    {
        Dir::make($this->tmp);

        $this->assertEquals(is_writable($this->tmp), Dir::isWritable($this->tmp));
    }

    public function testReadable()
    {
        Dir::make($this->tmp);

        $this->assertEquals(is_readable($this->tmp), Dir::isReadable($this->tmp));
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The target directory
     */
    public function testCopyExists()
    {
        $src    = static::FIXTURES . '/copy';
        $target = static::FIXTURES . '/copy';

        Dir::copy($src, $target);
    }

}
