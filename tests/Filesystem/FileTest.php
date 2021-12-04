<?php

namespace Kirby\Filesystem;

use PHPUnit\Framework\TestCase as TestCase;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Filesystem\File
 */
class FileTest extends TestCase
{
    // used for the mocks
    public static $block = [];

    protected $fixtures = __DIR__ . '/fixtures/files';
    protected $tmp      = __DIR__ . '/tmp';

    protected function setUp(): void
    {
        Dir::make($this->tmp);

        static::$block = [];
    }

    public function tearDown(): void
    {
        if (file_exists($this->tmp . '/unreadable.txt') === true) {
            chmod($this->tmp . '/unreadable.txt', 0755);
        }

        Dir::remove($this->tmp);
        static::$block = [];
    }

    protected function _file($file = 'test.js')
    {
        return new File([
            'root' => $this->fixtures . '/' . $file
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::root
     * @covers ::url
     */
    public function testConstruct()
    {
        $file = new File([
            'root' => '/dev/null/test.pdf',
            'url'  => 'https://foo.bar/test.pdf'
        ]);

        $this->assertSame('/dev/null/test.pdf', $file->root());
        $this->assertSame('https://foo.bar/test.pdf', $file->url());

        $file = new File('/dev/null/test.js');
        $this->assertSame('/dev/null/test.js', $file->root());
        $this->assertNull($file->url());
    }

    /**
     * @covers ::__construct
     * @covers ::root
     * @covers ::url
     */
    public function testLegacyConstruct()
    {
        // @todo 4.0.0 Remove
        $file = new File(
            '/dev/null/test.pdf',
            'https://home.io/test.pdf'
        );
        $this->assertSame('/dev/null/test.pdf', $file->root());
        $this->assertSame('https://home.io/test.pdf', $file->url());
    }

    /**
     * @covers ::base64
     */
    public function testBase64()
    {
        $file   = $this->_file('real.svg');
        $base64 = file_get_contents($this->fixtures . '/real.svg.base64');
        $this->assertSame($base64, $file->base64());
    }

    /**
     * @covers ::copy
     */
    public function testCopy()
    {
        $oldRoot = $this->tmp . '/test.txt';
        $newRoot = $this->tmp . '/awesome.txt';

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

    /**
     * @covers ::copy
     */
    public function testCopyToExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be copied');

        $file = $this->_file();
        $file->copy($this->fixtures . '/folder/b.txt');
    }

    /**
     * @covers ::copy
     */
    public function testCopyNonExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be copied');

        $file = $this->_file('a.txt');
        $file->copy($this->tmp . '/b.txt');
    }

    /**
     * @covers ::copy
     */
    public function testCopyFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be copied');

        static::$block[] = 'copy';
        $file = new File($this->tmp . '/awesome.txt');
        $file->copy($this->tmp . '/copied.txt');
    }

    /**
     * @covers ::dataUri
     */
    public function testDataUri()
    {
        $file = $this->_file('real.svg');
        $base64 = file_get_contents($this->fixtures . '/real.svg.base64');
        $this->assertSame('data:image/svg+xml;base64,' . $base64, $file->dataUri());
    }

    /**
     * @covers ::dataUri
     */
    public function testDataUriRaw()
    {
        $file = $this->_file('real.svg');
        $encoded = rawurlencode($file->read());
        $this->assertSame('data:image/svg+xml,' . $encoded, $file->dataUri(false));
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $file = new File($this->tmp . '/test.txt');

        $file->write('test');
        $this->assertTrue($file->exists());

        $file->delete();
        $this->assertFalse($file->exists());
    }

    /**
     * @covers ::delete
     */
    public function testDeleteNotExisting()
    {
        $file = new File('test.txt');
        $this->assertFalse($file->exists());
        $this->assertTrue($file->delete());
    }

    /**
     * @covers ::delete
     */
    public function testDeleteFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be deleted');

        static::$block[] = 'unlink';
        $file = new File($this->fixtures . '/test.js');
        $file->delete();
    }

    /**
     * @covers ::download
     */
    public function testDownload()
    {
        $file = $this->_file();
        $this->assertIsString($file->download());
        $this->assertIsString($file->download('meow.jpg'));
    }

    /**
     * @covers ::exists
     */
    public function testExists()
    {
        $file = $this->_file();
        $this->assertTrue($file->exists());

        $file = new File('does-not-exist.jpg');
        $this->assertFalse($file->exists());
    }

    /**
     * @covers ::extension
     */
    public function testExtension()
    {
        $file = $this->_file();
        $this->assertSame('js', $file->extension());
    }

    /**
     * @covers ::filename
     */
    public function testFilename()
    {
        $file = $this->_file();
        $this->assertSame('test.js', $file->filename());
    }

    /**
     * @covers ::hash
     */
    public function testHash()
    {
        $file = $this->_file();
        $this->assertIsString($file->hash());
    }

    /**
     * @covers ::header
     */
    public function testHeader()
    {
        $file = $this->_file();
        $this->assertInstanceOf('Kirby\Http\Response', $file->header(false));
    }

    /**
     * @covers ::header
     */
    public function testHeaderSend()
    {
        $file = $this->_file();
        $this->assertNull($file->header());
    }

    /**
     * @covers ::html
     */
    public function testHtml()
    {
        $file = new File([
            'root' => $this->fixtures . '/blank.pdf',
            'url'  => 'https://foo.bar/blank.pdf'
        ]);
        $this->assertSame('<a href="https://foo.bar/blank.pdf">foo.bar/blank.pdf</a>', $file->html());
    }

    /**
     * @covers ::is
     */
    public function testIs()
    {
        $file = $this->_file();
        $this->assertTrue($file->is('text/plain'));
        $this->assertFalse($file->is('image/jpeg'));

        $this->assertTrue($file->is('js'));
        $this->assertFalse($file->is('jpg'));
    }

    /**
     * @covers ::isResizable
     */
    public function testIsResizable()
    {
        $file = $this->_file();
        $this->assertFalse($file->isResizable());
    }

    /**
     * @covers ::isViewable
     */
    public function testIsViewable()
    {
        $file = $this->_file();
        $this->assertFalse($file->isViewable());
    }

    /**
     * @covers ::isWritable
     */
    public function testIsWritable()
    {
        $file = $this->_file();
        $this->assertTrue($file->isWritable());

        $file = new File($this->fixtures . '/permissions/unwritable/test.txt');
        $this->assertFalse($file->isWritable());

        $file = new File($this->fixtures . '/permissions/unwritable.txt');
        $this->assertFalse($file->isWritable());
    }

    /**
     * @covers ::kirby
     */
    public function testKirby()
    {
        $file = $this->_file();
        $this->assertInstanceOf('Kirby\Cms\App', $file->kirby());
    }

    /**
     * @covers ::match
     */
    public function testMatch()
    {
        $rules = [
            'miMe'        => ['image/png', 'image/jpeg', 'application/pdf'],
            'extensION'   => ['jpg', 'pdf'],
            'tYPe'        => ['image', 'video'],
            'MINsize'     => 20000,
            'maxSIze'     => 25000
        ];

        $this->assertTrue($this->_file('cat.jpg')->match($rules));
    }

    /**
     * @covers \Kirby\Filesystem\File::match
     */
    public function testMatchMimeException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid mime type: text/plain');

        $this->_file()->match(['mime' => ['image/png', 'application/pdf']]);
    }

    /**
     * @covers \Kirby\Filesystem\File::match
     */
    public function testMatchExtensionException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid extension: js');

        $this->_file()->match(['extension' => ['png', 'pdf']]);
    }

    /**
     * @covers \Kirby\Filesystem\File::match
     */
    public function testMatchTypeException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid file type: code');

        $this->_file()->match(['type' => ['document', 'video']]);
    }

    /**
     * @covers ::mime
     */
    public function testMime()
    {
        $file = $this->_file();
        $this->assertSame('text/plain', $file->mime());
    }

    /**
     * @covers ::modified
     */
    public function testModified()
    {
        // existing file
        $file = $this->_file();
        $this->assertSame(F::modified($file->root()), $file->modified());

        $this->assertSame(@strftime('%d.%m.%Y', F::modified($file->root())), $file->modified('%d.%m.%Y', 'strftime'));

        // non-existing file
        $file = $this->_file('does/not/exist.js');
        $this->assertFalse($file->modified());
    }

    /**
     * @covers ::move
     */
    public function testMove()
    {
        $oldRoot = $this->tmp . '/test.txt';
        $newRoot = $this->tmp . '/awesome.txt';

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

    /**
     * @covers ::move
     */
    public function testMoveToExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be moved');

        $file = $this->_file();
        $file->move($this->fixtures . '/folder/b.txt');
    }

    /**
     * @covers ::move
     */
    public function testMoveNonExisting()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be moved');

        $file = $this->_file('a.txt');
        $file->move($this->fixtures . '/b.txt');
    }

    /**
     * @covers ::move
     */
    public function testMoveFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be moved');

        static::$block[] = 'rename';
        $file = new File($this->tmp . '/awesome.txt');
        $file->move($this->tmp . '/moved.txt');
    }

    /**
     * @covers ::name
     */
    public function testName()
    {
        $file = $this->_file();
        $this->assertSame('test', $file->name());
    }

    /**
     * @covers ::niceSize
     */
    public function testNiceSize()
    {
        // existing file
        $file = $this->_file('test.js');
        $this->assertSame('14 B', $file->niceSize());

        // non-existing file
        $file = $this->_file('does/not/exist.js');
        $this->assertSame('0 KB', $file->niceSize());
    }

    /**
     * @covers ::read
     */
    public function testRead()
    {
        $file = $this->_file();
        $this->assertSame(file_get_contents($file->root()), $file->read());
    }

    /**
     * @covers ::read
     */
    public function testReadNotExist()
    {
        $file = $this->_file('missing.txt');
        $this->assertFalse($file->read());
    }

    /**
     * @covers ::read
     */
    public function testReadUnreadble()
    {
        $file = new File($this->tmp . '/unreadable.txt');
        $file->write('test');
        chmod($file->root(), 0000);
        $this->assertFalse($file->read());
    }

    /**
     * @covers ::rename
     */
    public function testRename()
    {
        $file = new File($this->tmp . '/test.js');
        $file->write('test');

        $renamed = $file->rename('awesome');
        $this->assertSame('awesome.js', $renamed->filename());
        $this->assertSame('awesome', $renamed->name());
    }

    /**
     * @covers ::rename
     */
    public function testRenameFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The file: "' . $this->fixtures . '/test.js" could not be renamed to: "awesome"');

        static::$block[] = 'rename';
        $file = $this->_file();
        $renamed = $file->rename('awesome');
    }

    /**
     * @covers ::rename
     */
    public function testRenameSameRoot()
    {
        $file = new File($this->tmp . '/test.txt');
        $file->write('test');
        $file->rename('test');

        $this->assertSame('test.txt', $file->filename());
        $this->assertSame($this->tmp . '/test.txt', $file->root());
    }

    /**
     * @covers ::root
     * @covers ::realpath
     */
    public function testRoot()
    {
        $file = $this->_file();
        $this->assertSame($this->fixtures . '/test.js', $file->root());
        $this->assertSame($this->fixtures . '/test.js', $file->realpath());
    }

    /**
     * @covers ::sanitizeContents
     */
    public function testSanitizeContentsValid()
    {
        $fixture = $this->fixtures . '/clean.svg';
        $tmp     = $this->tmp . '/clean.svg';
        copy($fixture, $tmp);

        $file = new File($tmp);
        $this->assertNull($file->sanitizeContents());
        $this->assertNull($file->sanitizeContents(true));
        $this->assertNull($file->sanitizeContents(false));

        $this->assertFileEquals($fixture, $tmp);
    }

    /**
     * @covers ::sanitizeContents
     */
    public function testSanitizeContentsWrongType()
    {
        $fixture = $this->fixtures . '/real.svg';
        $tmp     = $this->tmp . '/real.svg';
        copy($fixture, $tmp);

        $file = new File($tmp);
        $file->sanitizeContents('xml');

        $this->assertFileEquals($this->fixtures . '/real.sanitized.svg', $tmp);
    }

    /**
     * @covers ::sanitizeContents
     */
    public function testSanitizeContentsMissingHandler()
    {
        $file = new File($this->fixtures . '/test.js');

        // lazy mode
        $file->sanitizeContents(true);

        // default mode
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "js"');

        $file->sanitizeContents();
    }

    /**
     * @covers ::size
     */
    public function testSize()
    {
        $file = $this->_file('test.js');
        $this->assertSame(14, $file->size());
    }

    /**
     * @covers ::sha1
     */
    public function testSha1()
    {
        $file = $this->_file('test.js');
        $this->assertSame('25f2d6df4f2a30f29f6f80da1e95011044b0b8f7', $file->sha1());
    }

    /**
     * @covers ::toArray
     * @covers ::__debugInfo
     */
    public function testToArray()
    {
        $file = $this->_file('blank.pdf');
        $this->assertSame('blank.pdf', $file->toArray()['filename']);
        $this->assertSame('blank', $file->toArray()['name']);
        $this->assertSame('pdf', $file->toArray()['extension']);
        $this->assertSame(false, $file->toArray()['isResizable']);
        $this->assertSame($file->toArray(), $file->__debugInfo());
    }

    /**
     * @covers ::toJson
     */
    public function testToJson()
    {
        $file = $this->_file();
        $this->assertIsString($json = $file->toJson());
        $this->assertSame('test.js', json_decode($json)->filename);
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $file = new File([
            'root' => $this->fixtures . '/blank.pdf',
            'url'  => $expected = 'https://foo.bar/blank.pdf'
        ]);

        $this->assertSame($expected, (string)$file);
        $this->assertSame($expected, $file->__toString());

        $file = new File([
            'root' => $expected = $this->fixtures . '/blank.pdf'
        ]);

        $this->assertSame($expected, (string)$file);
        $this->assertSame($expected, $file->__toString());

        $file = new File([]);

        $this->assertSame('', (string)$file);
        $this->assertSame('', $file->__toString());
    }

    /**
     * @covers ::type
     */
    public function testType()
    {
        $file = $this->_file();
        $this->assertSame('code', $file->type());
    }

    /**
     * @covers ::type
     */
    public function testTypeUnknown()
    {
        $file = $this->_file('test.kirby');
        $this->assertNull($file->type());
    }

    /**
     * @covers ::validateContents
     */
    public function testValidateContentsValid()
    {
        $file = new File($this->fixtures . '/real.svg');
        $this->assertNull($file->validateContents());
        $this->assertNull($file->validateContents(true));
        $this->assertNull($file->validateContents(false));
    }

    /**
     * @covers ::validateContents
     */
    public function testValidateContentsWrongType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "http://www.w3.org/2000/svg" is not allowed (around line 2)');

        $file = new File($this->fixtures . '/real.svg');
        $file->validateContents('xml');
    }

    /**
     * @covers ::validateContents
     */
    public function testValidateContentsMissingHandler()
    {
        $file = new File($this->fixtures . '/test.js');

        // lazy mode
        $file->validateContents(true);

        // default mode
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionMessage('Missing handler for type: "js"');

        $file->validateContents();
    }

    /**
     * @covers ::write
     */
    public function testWrite()
    {
        $root = $this->tmp . '/test.txt';

        $file = new File($root);
        $this->assertFalse($file->exists());

        $file->write('test');
        $this->assertTrue($file->exists());
        $this->assertSame('test', file_get_contents($file->root()));
    }

    /**
     * @covers ::write
     */
    public function testWriteUnwritable()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('is not writable');

        $file = new File($this->tmp . '/unwritable.txt');
        $file->write('test');
        chmod($file->root(), 0555);
        $file->write('kirby');
    }

    /**
     * @covers ::write
     */
    public function testWriteFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('could not be written');

        static::$block[] = 'file_put_contents';
        $file = new File($this->tmp . '/test.txt');
        $file->write('test');
    }
}
