<?php

namespace Kirby\File;

use PHPUnit\Framework\TestCase as TestCase;

class ImageTest extends TestCase
{
    protected function _file($file = 'cat.jpg')
    {
        return new Image([
            'root' => __DIR__ . '/fixtures/' . $file,
            'url'  => 'https://foo.bar/' . $file
        ]);
    }

    public function testDimensions()
    {
        // jpg
        $file = $this->_file();
        $this->assertInstanceOf('Kirby\Image\Dimensions', $file->dimensions());

        // svg with width and height
        $file = $this->_file('square.svg');
        $this->assertEquals(100, $file->dimensions()->width());
        $this->assertEquals(100, $file->dimensions()->height());

        // svg with viewBox
        $file = $this->_file('circle.svg');
        $this->assertEquals(50, $file->dimensions()->width());
        $this->assertEquals(50, $file->dimensions()->height());

        // webp
        $file = $this->_file('valley.webp');
        $this->assertEquals(550, $file->dimensions()->width());
        $this->assertEquals(368, $file->dimensions()->height());

        // non-image file
        $file = $this->_file('blank.pdf');
        $this->assertEquals(0, $file->dimensions()->width());
        $this->assertEquals(0, $file->dimensions()->height());

        // cached object
        $this->assertInstanceOf('Kirby\Image\Dimensions', $file->dimensions());
    }

    public function testExif()
    {
        $file = $this->_file();
        $this->assertInstanceOf('Kirby\Image\Exif', $file->exif());
        $this->assertInstanceOf('Kirby\Image\Exif', $file->exif());
    }

    public function testHeight()
    {
        $file = $this->_file();
        $this->assertSame(500, $file->height());
    }

    public function testHtml()
    {
        $file = $this->_file();
        $this->assertSame('<img alt="" src="https://foo.bar/cat.jpg">', $file->html());
    }

    public function testImagesize()
    {
        $file = $this->_file();
        $this->assertIsArray($file->imagesize());
        $this->assertSame(500, $file->imagesize()[0]);
    }

    public function testIsPortrait()
    {
        $file = $this->_file();
        $this->assertFalse($file->isPortrait());
    }

    public function testIsLandscape()
    {
        $file = $this->_file();
        $this->assertFalse($file->isLandscape());
    }

    public function testIsSquare()
    {
        $file = $this->_file();
        $this->assertTrue($file->isSquare());
    }

    public function testIsResizable()
    {
        $file = $this->_file();
        $this->assertTrue($file->isResizable());

        $file = $this->_file('test.heic');
        $this->assertFalse($file->isResizable());
    }

    public function testIsViewable()
    {
        $file = $this->_file();
        $this->assertTrue($file->isResizable());

        $file = $this->_file('test.heic');
        $this->assertFalse($file->isResizable());
    }

    public function testMatch()
    {
        $rules = [
            'miMe'        => ['image/png', 'image/jpeg', 'application/pdf'],
            'extensION'   => ['jpg', 'pdf'],
            'tYPe'        => ['image', 'video'],
            'MINsize'     => 20000,
            'maxSIze'     => 25000,
            'minheiGHt'   => 400,
            'maxHeight'   => 600,
            'minWIdth'    => 400,
            'maxwiDth'    => 600,
            'oriEntation' => 'square'
        ];

        $this->assertTrue($this->_file()->match($rules));
    }

    public function testMatchMimeException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid mime type: image/jpeg');

        $this->_file()->match(['mime' => ['image/png', 'application/pdf']]);
    }

    public function testMatchExtensionException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid extension: jpg');

        $this->_file()->match(['extension' => ['png', 'pdf']]);
    }

    public function testMatchTypeException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('Invalid file type: image');

        $this->_file()->match(['type' => ['document', 'video']]);
    }

    public function testMatchOrientationException()
    {
        $this->expectException('Kirby\Exception\Exception');
        $this->expectExceptionMessage('The orientation of the image must be "portrait"');

        $this->_file()->match(['orientation' => 'portrait']);
    }

    public function testRatio()
    {
        $image  = $this->_file();
        $this->assertEquals(1.0, $image->ratio());
    }

    public function testToArray()
    {
        $file = $this->_file();
        $this->assertSame('cat.jpg', $file->toArray()['filename']);
        $this->assertIsArray($file->toArray()['exif']);
        $this->assertIsArray($file->toArray()['dimensions']);
    }

    public function testWidth()
    {
        $file = $this->_file();
        $this->assertSame(500, $file->width());
    }
}
