<?php

namespace Kirby\FileSystem\File;

use Kirby\FileSystem\File;

function function_exists($function)
{
    if (in_array($function, FolderTest::$block)) {
        return false;
    }
    return \function_exists($function);
}

class FolderTest extends \PHPUnit\Framework\TestCase
{

    public static $block = [];

    protected function _mime($filename = 'test.js')
    {
        return new MimeType(dirname(__DIR__) . '/fixtures/files/' . $filename);
    }

    public function testFromFile()
    {
        $file = new File(dirname(__DIR__) . '/fixtures/files/test.js');
        $mime = new MimeType($file);
        $this->assertEquals('text/plain', $mime->name());
    }

    public function testFromPath()
    {
        $mime = $this->_mime();
        $this->assertEquals('text/plain', $mime->name());
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage File does not exist
     */
    public function testFromNonExistingFile()
    {
        $file = new File(dirname(__DIR__) . '/fixtures/files/foo.txt');
        $mime = new MimeType($file);
    }

    public function testGetFromFileInfo()
    {
        $mime = $this->_mime();
        $this->assertEquals('text/plain', $mime->name());
    }

    public function testGetFromMimeContentType()
    {
        static::$block = ['finfo_file'];
        $mime = $this->_mime();
        $this->assertEquals('text/plain', $mime->name());
        static::$block = [];
    }

    public function testGetFromSystem()
    {
        static::$block = ['finfo_file', 'mime_content_type'];
        $this->markTestIncomplete();
    }

    public function testGetFromExtension()
    {
        static::$block = ['finfo_file', 'mime_content_type'];
        $this->markTestIncomplete();
    }

    public function testIsSvg()
    {
        static::$block = [];

        $mime = $this->_mime('test.svg');
        $this->assertFalse($mime->isSvg());

        $mime = $this->_mime('real.svg');
        $this->assertTrue($mime->isSvg());
    }
}
