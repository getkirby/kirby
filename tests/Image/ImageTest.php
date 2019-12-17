<?php

namespace Kirby\Image;

use Kirby\Http\Response;

class ImageTest extends TestCase
{
    protected function _image($filename = 'cat.jpg', $url = 'http://getkirby.com/cat.jpg')
    {
        return new Image(static::FIXTURES . '/image/' . $filename, $url);
    }

    public function testConstruct()
    {
        $image = $this->_image();
        $this->assertEquals(static::FIXTURES . '/image/cat.jpg', $image->root());
        $this->assertEquals('http://getkirby.com/cat.jpg', $image->url());
    }

    public function testHeader()
    {
        $image  = $this->_image();
        $this->assertInstanceOf(Response::class, $image->header(false));
    }

    public function testHeaderSend()
    {
        $image  = $this->_image();
        $this->assertEquals('', $image->header());
    }

    public function testDownload()
    {
        $image  = $this->_image();
        $this->assertIsString($image->download());
        $this->assertIsString($image->download('meow.jpg'));
    }

    public function testExif()
    {
        $image  = $this->_image();
        $this->assertInstanceOf(Exif::class, $image->exif());
        $this->assertInstanceOf(Exif::class, $image->exif());
    }

    public function testImagesize()
    {
        $image  = $this->_image();
        $this->assertEquals([
            500,
            500,
            2,
            'width="500" height="500"',
            'bits'     => 8,
            'channels' => 3,
            'mime'     => 'image/jpeg'
        ], $image->imagesize());
    }

    public function testDimensions()
    {
        // jpg
        $image  = $this->_image();
        $this->assertInstanceOf(Dimensions::class, $image->dimensions());

        // svg with width and height
        $image  = $this->_image('square.svg');
        $this->assertEquals(100, $image->dimensions()->width());
        $this->assertEquals(100, $image->dimensions()->height());

        // svg with viewBox
        $image  = $this->_image('circle.svg');
        $this->assertEquals(50, $image->dimensions()->width());
        $this->assertEquals(50, $image->dimensions()->height());

        // webp
        $image  = $this->_image('valley.webp');
        $this->assertEquals(550, $image->dimensions()->width());
        $this->assertEquals(368, $image->dimensions()->height());

        // non-image file
        $image  = $this->_image('blank.pdf');
        $this->assertEquals(0, $image->dimensions()->width());
        $this->assertEquals(0, $image->dimensions()->height());

        // cached object
        $this->assertInstanceOf(Dimensions::class, $image->dimensions());
    }

    public function testWidth()
    {
        $image  = $this->_image();
        $this->assertEquals(500, $image->width());
    }

    public function testHeight()
    {
        $image  = $this->_image();
        $this->assertEquals(500, $image->height());
    }

    public function testRatio()
    {
        $image  = $this->_image();
        $this->assertEquals(1.0, $image->ratio());
    }

    public function testIsPortrait()
    {
        $image  = $this->_image();
        $this->assertFalse($image->isPortrait());
    }

    public function testIsLandscape()
    {
        $image  = $this->_image();
        $this->assertFalse($image->isLandscape());
    }

    public function testIsSquare()
    {
        $image  = $this->_image();
        $this->assertTrue($image->isSquare());
    }

    public function testOrientation()
    {
        $image  = $this->_image();
        $this->assertEquals('square', $image->orientation());
    }

    public function testHtml()
    {
        $image = $this->_image();
        $html  = $image->html();
        $this->assertEquals('<img alt="" src="http://getkirby.com/cat.jpg">', $html);
    }

    public function testToArray()
    {
        $image  = $this->_image();
        $this->assertIsArray($image->toArray());
    }

    public function testToJson()
    {
        $image  = $this->_image();
        $this->assertIsString($image->toJson());
    }

    public function testToString()
    {
        $image  = $this->_image();
        $this->assertEquals(__DIR__ . '/fixtures/image/cat.jpg', (string)$image);
    }

    public function testDebuginfo()
    {
        $image  = $this->_image();
        $this->assertIsArray($image->__debugInfo());
    }
}
