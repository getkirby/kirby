<?php

namespace Kirby\Image;

use Kirby\Exception\Exception;
use Kirby\Exception\LogicException;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Image
 */
class ImageTest extends TestCase
{
	protected function _image($file = 'cat.jpg')
	{
		return new Image([
			'root' => __DIR__ . '/fixtures/image/' . $file,
			'url'  => 'https://foo.bar/' . $file
		]);
	}

	/**
	 * @covers ::dimensions
	 */
	public function testDimensions()
	{
		// jpg
		$file = $this->_image();
		$this->assertInstanceOf(Dimensions::class, $file->dimensions());

		// svg with width and height
		$file = $this->_image('square.svg');
		$this->assertEquals(100, $file->dimensions()->width());
		$this->assertEquals(100, $file->dimensions()->height());

		// svg with viewBox
		$file = $this->_image('circle.svg');
		$this->assertEquals(50, $file->dimensions()->width());
		$this->assertEquals(50, $file->dimensions()->height());

		// webp
		$file = $this->_image('valley.webp');
		$this->assertEquals(550, $file->dimensions()->width());
		$this->assertEquals(368, $file->dimensions()->height());

		// non-image file
		$file = $this->_image('blank.pdf');
		$this->assertEquals(0, $file->dimensions()->width());
		$this->assertEquals(0, $file->dimensions()->height());

		// cached object
		$this->assertInstanceOf(Dimensions::class, $file->dimensions());
	}

	/**
	 * @covers ::exif
	 */
	public function testExif()
	{
		$file = $this->_image();
		$this->assertInstanceOf(Exif::class, $file->exif());
		// cached object
		$this->assertInstanceOf(Exif::class, $file->exif());
	}

	/**
	 * @covers ::height
	 */
	public function testHeight()
	{
		$file = $this->_image();
		$this->assertSame(500, $file->height());
	}

	/**
	 * @covers ::html
	 */
	public function testHtml()
	{
		$file = $this->_image();
		$this->assertSame('<img alt="" src="https://foo.bar/cat.jpg">', $file->html());
	}

	/**
	 * @covers ::html
	 */
	public function testHtmlWithoutUrl()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Calling Image::html() requires that the URL property is not null');
		$file = new Image(['root' => __DIR__ . '/fixtures/image/cat.jpg']);
		$file->html();
	}

	/**
	 * @covers ::imagesize
	 */
	public function testImagesize()
	{
		$file = $this->_image();
		$this->assertIsArray($file->imagesize());
		$this->assertSame(500, $file->imagesize()[0]);
	}

	/**
	 * @covers ::isPortrait
	 */
	public function testIsPortrait()
	{
		$file = $this->_image();
		$this->assertFalse($file->isPortrait());
	}

	/**
	 * @covers ::isLandscape
	 */
	public function testIsLandscape()
	{
		$file = $this->_image();
		$this->assertFalse($file->isLandscape());
	}

	/**
	 * @covers ::isSquare
	 */
	public function testIsSquare()
	{
		$file = $this->_image();
		$this->assertTrue($file->isSquare());
	}

	/**
	 * @covers ::isresizable
	 */
	public function testIsResizable()
	{
		$file = $this->_image();
		$this->assertTrue($file->isResizable());

		$file = $this->_image('test.heic');
		$this->assertFalse($file->isResizable());
	}

	/**
	 * @covers ::isViewable
	 */
	public function testIsViewable()
	{
		$file = $this->_image();
		$this->assertTrue($file->isResizable());

		$file = $this->_image('test.heic');
		$this->assertFalse($file->isResizable());
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
			'maxSIze'     => 25000,
			'minheiGHt'   => 400,
			'maxHeight'   => 600,
			'minWIdth'    => 400,
			'maxwiDth'    => 600,
			'oriEntation' => 'square'
		];

		$this->assertTrue($this->_image()->match($rules));
	}

	/**
	 * @covers ::match
	 */
	public function testMatchOrientationException()
	{
		// Make sure i18n files are loaded
		$kirby = kirby();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The orientation of the image must be "portrait"');

		$this->_image()->match(['orientation' => 'portrait']);
	}

	/**
	 * @covers ::orientation
	 */
	public function testOrientation()
	{
		$file = $this->_image();
		$this->assertSame('square', $file->orientation());
	}

	/**
	 * @covers ::ratio
	 */
	public function testRatio()
	{
		$image  = $this->_image();
		$this->assertEquals(1.0, $image->ratio());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		$file = $this->_image();
		$this->assertSame('cat.jpg', $file->toArray()['filename']);
		$this->assertIsArray($file->toArray()['exif']);
		$this->assertIsArray($file->toArray()['dimensions']);
	}

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$file = $this->_image();
		$expected = '<img alt="" src="https://foo.bar/cat.jpg">';
		$this->assertSame($expected, $file->__toString());
		$this->assertSame($expected, (string)$file);
	}

	/**
	 * @covers ::width
	 */
	public function testWidth()
	{
		$file = $this->_image();
		$this->assertSame(500, $file->width());
	}
}
