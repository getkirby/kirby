<?php

namespace Kirby\File;

use PHPUnit\Framework\TestCase as TestCase;

class FileTest extends TestCase
{
    protected function _file($file = 'blank.pdf')
    {
        return new File([
            'root' => __DIR__ . '/fixtures/' . $file,
            'url'  => 'https://foo.bar/' . $file
        ]);
    }

    public function testConstruct()
    {
        $file = new File([
            'root' => '/dev/null/test.pdf',
            'url'  => 'https://foo.bar/test.pdf'
        ]);

        $this->assertSame('/dev/null/test.pdf', $file->root());
        $this->assertSame('https://foo.bar/test.pdf', $file->url());
    }

    public function testLegacyConstruct()
    {
        // @todo 4.0.0 Remove
        $file = new \Kirby\File\Image(
            '/dev/null/test.pdf',
            'https://home.io/test.pdf'
        );
        $this->assertSame('/dev/null/test.pdf', $file->root());
        $this->assertSame('https://home.io/test.pdf', $file->url());
    }

    public function testHtml()
    {
        $file = $this->_file();
        $this->assertSame('<a href="https://foo.bar/blank.pdf">foo.bar/blank.pdf</a>', $file->html());
    }

    public function testIsResizable()
    {
        $file = $this->_file();
        $this->assertFalse($file->isResizable());
    }

    public function testIsViewable()
    {
        $file = $this->_file();
        $this->assertFalse($file->isViewable());
    }

    public function testKirby()
    {
        $file = $this->_file();
        $this->assertInstanceOf('Kirby\Cms\App', $file->kirby());
    }

    public function testModified()
    {
        $file = $this->_file();
        $time = filemtime(__DIR__ . '/fixtures/blank.pdf');
        $this->assertSame($time, $file->modified());
    }

    public function testToArray()
    {
        $file = $this->_file();
        $this->assertSame('blank.pdf', $file->toArray()['filename']);
        $this->assertSame('blank', $file->toArray()['name']);
        $this->assertSame('pdf', $file->toArray()['extension']);
        $this->assertSame(false, $file->toArray()['isResizable']);
    }
}
