<?php

namespace Kirby\Toolkit;

require_once __DIR__ . '/mocks.php';

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

    public function testBase64()
    {
        $file  = $this->_file('real.svg');
        $base64 = file_get_contents(static::FIXTURES . '/real.svg.base64');
        $this->assertSame($base64, $file->base64());
    }

    public function testDataUri()
    {
        $file = $this->_file('real.svg');
        $base64 = file_get_contents(static::FIXTURES . '/real.svg.base64');
        $this->assertSame('data:image/svg+xml;base64,' . $base64, $file->dataUri());
    }

    public function testDataUriRaw()
    {
        $file = $this->_file('real.svg');
        $encoded = rawurlencode($file->read());
        $this->assertSame('data:image/svg+xml,' . $encoded, $file->dataUri(false));
    }

    public function testDownload()
    {
        $file = $this->_file();
        $this->assertIsString($file->download());
        $this->assertIsString($file->download('meow.jpg'));
    }

    public function testExists()
    {
        $file = $this->_file();
        $this->assertTrue($file->exists());

        $file = new File('does-not-exist.jpg');
        $this->assertFalse($file->exists());
    }

    public function testHeader()
    {
        $file = $this->_file();
        $this->assertInstanceOf('Kirby\Http\Response', $file->header(false));
    }

    public function testHeaderSend()
    {
        $file = $this->_file();
        $this->assertSame('', $file->header());
    }

    public function testRoot()
    {
        $file = $this->_file();
        $this->assertSame(static::FIXTURES . '/test.js', $file->root());
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
        $this->assertSame('test.js', $file->filename());
    }

    public function testName()
    {
        $file = $this->_file();
        $this->assertSame('test', $file->name());
    }

    public function testExtension()
    {
        $file = $this->_file();
        $this->assertSame('js', $file->extension());
    }

    public function testMime()
    {
        $file = $this->_file();
        $this->assertSame('text/plain', $file->mime());
    }

    public function testType()
    {
        $file = $this->_file();
        $this->assertSame('code', $file->type());
    }

    public function testUnknownType()
    {
        $file = $this->_file('test.kirby');
        $this->assertNull($file->type());
    }

    public function testSize()
    {
        $file = $this->_file('test.js');
        $this->assertSame(14, $file->size());
    }

    public function testNiceSize()
    {
        // existing file
        $file = $this->_file('test.js');
        $this->assertSame('14 B', $file->niceSize());

        // non-existing file
        $file = $this->_file('does/not/exist.js');
        $this->assertSame('0 KB', $file->niceSize());
    }

    public function testModified()
    {
        // existing file
        $file = $this->_file('test.js');
        $this->assertSame(F::modified($file->root()), $file->modified());

        $this->assertSame(strftime('%d.%m.%Y', F::modified($file->root())), $file->modified('%d.%m.%Y', 'strftime'));

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
        $this->assertSame('test', file_get_contents($file->root()));
    }

    public function testWriteUnwritable()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('is not writable');

        $file = new File(static::FIXTURES . '/tmp/unwritable.txt');
        $file->write('test');
        chmod($file->root(), 555);
        $file->write('kirby');
    }

    public function testWriteFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be written');

        static::$block[] = 'file_put_contents';
        $file = new File(static::FIXTURES . '/tmp/test.txt');
        $file->write('test');
    }

    public function testRead()
    {
        $file = $this->_file();
        $this->assertSame(file_get_contents($file->root()), $file->read());
    }

    public function testReadNotExist()
    {
        $file = $this->_file('missing.txt');
        $this->assertFalse($file->read());
    }

    public function testReadUnreadble()
    {
        $file = new File(static::FIXTURES . '/tmp/unreadable.txt');
        $file->write('test');
        chmod($file->root(), 000);
        $this->assertFalse($file->read());
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
        $this->assertSame($oldRoot, $file->root());

        $moved = $file->move($newRoot);

        $this->assertFalse(file_exists($oldRoot));
        $this->assertTrue(file_exists($newRoot));
        $this->assertSame($newRoot, $moved->root());
    }

    public function testMoveToExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be moved');

        $file = $this->_file();
        $file->move(static::FIXTURES . '/folder/b.txt');
    }

    public function testMoveNonExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be moved');

        $file = $this->_file('a.txt');
        $file->move(static::FIXTURES . '/b.txt');
    }

    public function testMoveFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be moved');

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
        $this->assertSame($oldRoot, $file->root());

        $new = $file->copy($newRoot);

        $this->assertTrue(file_exists($oldRoot));
        $this->assertTrue(file_exists($newRoot));
        $this->assertInstanceOf(File::class, $new);
        $this->assertSame($newRoot, $new->root());
    }

    public function testCopyToExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be copied');

        $file = $this->_file();
        $file->copy(static::FIXTURES . '/folder/b.txt');
    }

    public function testCopyNonExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be copied');

        $file = $this->_file('a.txt');
        $file->copy(static::FIXTURES . '/b.txt');
    }

    public function testCopyFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be copied');

        static::$block[] = 'copy';
        $file = new File(static::FIXTURES . '/tmp/awesome.txt');
        $file->copy(static::FIXTURES . '/tmp/copied.txt');
    }

    public function testRename()
    {
        $file = $this->_file();
        $renamed = $file->rename('awesome');

        $this->assertSame('awesome.js', $renamed->filename());
        $this->assertSame('awesome', $renamed->name());

        $renamed->rename('test');
    }

    public function testRenameSameRoot()
    {
        $file = new File(static::FIXTURES . '/tmp/test.txt');
        $file->write('test');
        $file->rename('test');

        $this->assertSame('test.txt', $file->filename());
        $this->assertSame(static::FIXTURES . '/tmp/test.txt', $file->root());

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

    public function testDeleteFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be deleted');

        static::$block[] = 'unlink';
        $file = new File(static::FIXTURES . '/tmp/awesome.txt');
        $file->delete();
    }

    public function testValidateContentsValid()
    {
        $file = new File(static::FIXTURES . '/real.svg');
        $this->assertNull($file->validateContents());
        $this->assertNull($file->validateContents(true));
        $this->assertNull($file->validateContents(false));
    }

    public function testValidateContentsWrongType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace is not allowed in XML files (around line 2)');

        $file = new File(static::FIXTURES . '/real.svg');
        $file->validateContents('xml');
    }

    public function testValidateContentsMissingHandler()
    {
        $file = new File(static::FIXTURES . '/test.js');

        // lazy mode
        $file->validateContents(true);

        // default mode
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "js"');

        $file->validateContents();
    }

    public function testToArray()
    {
        $file = $this->_file();
        $this->assertSame('test.js', $file->toArray()['filename']);
        $this->assertSame('js', $file->toArray()['extension']);
    }

    public function testToString()
    {
        $file = $this->_file();
        $this->assertIsString($json = $file->toJson());
        $this->assertSame('test.js', json_decode($json)->filename);
    }

    public static function tearDownAfterClass(): void
    {
        @chmod(static::FIXTURES . '/tmp/unreadable.txt', 755);
        @\unlink(static::FIXTURES . '/tmp/unreadable.txt');

        @chmod(static::FIXTURES . '/tmp/unwritable.txt', 755);
        @\unlink(static::FIXTURES . '/tmp/unwritable.txt');
    }
}
