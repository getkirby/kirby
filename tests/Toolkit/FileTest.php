<?php

namespace Kirby\Toolkit;

function blockMethod($method, $args)
{
    if (in_array($method, FileTest::$block)) {
        return false;
    }
    return call_user_func_array('\\' . $method, $args);
}

function file_put_contents($file, $content)
{
    return blockMethod('file_put_contents', [$file, $content]);
}

function rename($old, $new)
{
    return blockMethod('rename', [$old, $new]);
}

function copy($old, $new)
{
    return blockMethod('copy', [$old, $new]);
}

function unlink($file)
{
    return blockMethod('unlink', [$file]);
}

class FileTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures/files';

    public static $block = [];

    protected function setUp(): void
    {
        static::$block = [];
    }

    protected function _file($filename = 'test.js')
    {
        return new File(static::FIXTURES . '/' . $filename);
    }

    public function testRoot()
    {
        $file = $this->_file();
        $this->assertEquals(static::FIXTURES . '/test.js', $file->root());
    }

    public function testExists()
    {
        $file = $this->_file();
        $this->assertTrue($file->exists());

        $file = new File('does-not-exist.jpg');
        $this->assertFalse($file->exists());
    }

    public function testWritable()
    {
        $file = $this->_file();
        $this->assertTrue($file->isWritable());

        $file = new File(static::FIXTURES . '/permissions/unwritable/test.txt');
        $this->assertFalse($file->isWritable());

        $file = new File(static::FIXTURES . '/permissions/unwritable.txt');
        $this->assertFalse($file->isWritable());
    }

    public function testFilename()
    {
        $file = $this->_file();
        $this->assertEquals('test.js', $file->filename());
    }

    public function testName()
    {
        $file = $this->_file();
        $this->assertEquals('test', $file->name());
    }

    public function testExtension()
    {
        $file = $this->_file();
        $this->assertEquals('js', $file->extension());
    }

    public function testMime()
    {
        $file = $this->_file();
        $this->assertEquals('text/plain', $file->mime());
    }

    public function testType()
    {
        $file = $this->_file();
        $this->assertEquals('code', $file->type());
    }

    public function testUnknownType()
    {
        $file = $this->_file('test.kirby');
        $this->assertNull($file->type());
    }

    public function testSize()
    {
        $file = $this->_file('test.js');
        $this->assertEquals(14, $file->size());
    }

    public function testNiceSize()
    {
        // existing file
        $file = $this->_file('test.js');
        $this->assertEquals('14 B', $file->niceSize());

        // non-existing file
        $file = $this->_file('does/not/exist.js');
        $this->assertEquals('0 kB', $file->niceSize());
    }

    public function testModified()
    {
        // existing file
        $file = $this->_file('test.js');
        $this->assertEquals(F::modified($file->root()), $file->modified());

        $this->assertEquals(strftime('%d.%m.%Y', F::modified($file->root())), $file->modified('%d.%m.%Y', 'strftime'));

        // non-existing file
        $file = $this->_file('does/not/exist.js');
        $this->assertFalse($file->modified());
    }

    public function testWrite()
    {
        $root = static::FIXTURES . '/tmp/test.txt';

        // clean up
        @unlink($root);

        $file = new File($root);

        $this->assertFalse($file->exists());

        $file->write('test');

        $this->assertTrue($file->exists());
        $this->assertEquals('test', file_get_contents($file->root()));
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage is not writable
     */
    public function testWriteUnwritable()
    {
        $file = new File(static::FIXTURES . '/tmp/unwritable.txt');
        $file->write('test');
        chmod($file->root(), 555);
        $file->write('kirby');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be written
     */
    public function testWriteFail()
    {
        static::$block[] = 'file_put_contents';
        $file = new File(static::FIXTURES . '/tmp/test.txt');
        $file->write('test');
    }

    public function testRead()
    {
        $file = $this->_file();
        $this->assertEquals(file_get_contents($file->root()), $file->read());
    }

    public function testReadNotExist()
    {
        $file = $this->_file('missing.txt');
        $this->assertEquals(null, $file->read());
    }

    public function testReadUnreadble()
    {
        $file = new File(static::FIXTURES . '/tmp/unreadable.txt');
        $file->write('test');
        chmod($file->root(), 000);
        $this->assertEquals(null, $file->read());
    }

    public function testMove()
    {
        $oldRoot = static::FIXTURES . '/tmp/test.txt';
        $newRoot = static::FIXTURES . '/tmp/awesome.txt';

        @unlink($oldRoot);
        @unlink($newRoot);

        $file = new File($oldRoot);
        $file->write('test');

        $this->assertTrue(file_exists($oldRoot));
        $this->assertFalse(file_exists($newRoot));
        $this->assertEquals($oldRoot, $file->root());

        $moved = $file->move($newRoot);

        $this->assertFalse(file_exists($oldRoot));
        $this->assertTrue(file_exists($newRoot));
        $this->assertEquals($newRoot, $moved->root());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be moved
     */
    public function testMoveToExisting()
    {
        $file = $this->_file();
        $file->move(static::FIXTURES . '/folder/b.txt');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be moved
     */
    public function testMoveNonExisting()
    {
        $file = $this->_file('a.txt');
        $file->move(static::FIXTURES . '/b.txt');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be moved
     */
    public function testMoveFail()
    {
        static::$block[] = 'rename';
        $file = new File(static::FIXTURES . '/tmp/awesome.txt');
        $file->move(static::FIXTURES . '/tmp/moved.txt');
    }

    public function testCopy()
    {
        $oldRoot = static::FIXTURES . '/tmp/test.txt';
        $newRoot = static::FIXTURES . '/tmp/awesome.txt';

        @unlink($oldRoot);
        @unlink($newRoot);

        $file = new File($oldRoot);
        $file->write('test');

        $this->assertTrue(file_exists($oldRoot));
        $this->assertFalse(file_exists($newRoot));
        $this->assertEquals($oldRoot, $file->root());

        $new = $file->copy($newRoot);

        $this->assertTrue(file_exists($oldRoot));
        $this->assertTrue(file_exists($newRoot));
        $this->assertInstanceOf(File::class, $new);
        $this->assertEquals($newRoot, $new->root());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be copied
     */
    public function testCopyToExisting()
    {
        $file = $this->_file();
        $file->copy(static::FIXTURES . '/folder/b.txt');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be copied
     */
    public function testCopyNonExisting()
    {
        $file = $this->_file('a.txt');
        $file->copy(static::FIXTURES . '/b.txt');
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be copied
     */
    public function testCopyFail()
    {
        static::$block[] = 'copy';
        $file = new File(static::FIXTURES . '/tmp/awesome.txt');
        $file->copy(static::FIXTURES . '/tmp/copied.txt');
    }

    public function testRename()
    {
        $file = $this->_file();
        $renamed = $file->rename('awesome');

        $this->assertEquals('awesome.js', $renamed->filename());
        $this->assertEquals('awesome', $renamed->name());

        $renamed->rename('test');
    }

    public function testRenameSameRoot()
    {
        $file = new File(static::FIXTURES . '/tmp/test.txt');
        $file->write('test');
        $file->rename('test');

        $this->assertEquals('test.txt', $file->filename());
        $this->assertEquals(static::FIXTURES . '/tmp/test.txt', $file->root());

        // clean up
        @unlink($file->root());
    }

    public function testDelete()
    {
        $root = static::FIXTURES . '/tmp/test.txt';

        // clean up
        @unlink($root);

        $file = new File($root);
        $file->write('test');

        $this->assertTrue($file->exists());

        $file->delete();

        $this->assertFalse($file->exists());
    }

    public function testDeleteNotExisting()
    {
        $file   = new File('test.txt');
        $this->assertFalse($file->exists());
        $this->assertTrue($file->delete());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage could not be deleted
     */
    public function testDeleteFail()
    {
        static::$block[] = 'unlink';
        $file = new File(static::FIXTURES . '/tmp/awesome.txt');
        $file->delete();
    }

    public static function tearDownAfterClass()
    {
        @chmod(static::FIXTURES . '/tmp/unreadable.txt', 755);
        @\unlink(static::FIXTURES . '/tmp/unreadable.txt');

        @chmod(static::FIXTURES . '/tmp/unwritable.txt', 755);
        @\unlink(static::FIXTURES . '/tmp/unwritable.txt');
    }
}
