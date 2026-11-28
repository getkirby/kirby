<?php

namespace Kirby\Filesystem;

use Kirby\Exception\Exception;
use Kirby\Exception\LogicException;
use Kirby\Image\Dimensions;
use Kirby\Image\Exif;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileImageTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/../Image/fixtures';

	protected function _image($file = 'cat.jpg')
	{
		return new File([
			'root' => static::FIXTURES . '/image/' . $file,
			'url'  => 'https://foo.bar/' . $file
		]);
	}

	public function testDimensions(): void
	{
		// jpg
		$file = $this->_image();
		$this->assertInstanceOf(Dimensions::class, $file->dimensions());

		// svg with width and height
		$file = $this->_image('square.svg');
		$this->assertSame(100, $file->dimensions()->width());
		$this->assertSame(100, $file->dimensions()->height());

		// svg with viewBox
		$file = $this->_image('circle.svg');
		$this->assertSame(50, $file->dimensions()->width());
		$this->assertSame(50, $file->dimensions()->height());

		// webp
		$file = $this->_image('valley.webp');
		$this->assertSame(550, $file->dimensions()->width());
		$this->assertSame(368, $file->dimensions()->height());

		// non-image file
		$file = $this->_image('blank.pdf');
		$this->assertNull($file->dimensions());
		$this->assertNull($file->width());
		$this->assertNull($file->height());
		$this->assertFalse($file->isSquare());

		// cached object
		$file = $this->_image();
		$this->assertSame($file->dimensions(), $file->dimensions());
	}

	public function testDimensionsDoesNotReadHeaderWithoutImageSignature(): void
	{
		foreach (['circle.svg' => 50, 'invalid.jpg' => null] as $filename => $size) {
			$file = new class (['root' => static::FIXTURES . '/image/' . $filename]) extends File {
				public function imagesize(): array|false
				{
					throw new LogicException('imagesize() must not be called');
				}
			};

			$this->assertSame($size, $file->dimensions()?->width());
			$this->assertSame($size, $file->dimensions()?->height());
		}
	}

	public function testDimensionsReadsTypeFromImageHeader(): void
	{
		$file = new class (['root' => static::FIXTURES . '/image/cat.jpg']) extends File {
			public int $mimeCalls = 0;

			public function mime(): string|null
			{
				$this->mimeCalls++;
				return parent::mime();
			}
		};

		$this->assertSame(500, $file->dimensions()->width());
		$this->assertSame(500, $file->dimensions()->height());
		$this->assertSame(0, $file->mimeCalls);
	}

	public function testDimensionsUsesMimeForOtherTypes(): void
	{
		$file = new class (['root' => static::FIXTURES . '/image/onigiri-adobe-rgb-gps.avif']) extends File {
			public function mime(): string|null
			{
				return 'image/heif';
			}
		};

		$this->assertNull($file->dimensions());
		$this->assertFalse($file->isResizable());
	}

	public function testDimensionsWithInvalidGif(): void
	{
		$file = $this->_image('invalid.gif');

		$this->assertNull($file->dimensions());
		$this->assertFalse($file->isResizable());
	}

	public function testDimensionsWithInvalidPngChunk(): void
	{
		$file = $this->_image('invalid-chunk.png');

		$this->assertNull($file->dimensions());
		$this->assertFalse($file->isResizable());
	}

	public function testDimensionsWithMissingFile(): void
	{
		$file = $this->_image('does-not-exist.jpg');

		$this->assertSame(0, $file->dimensions()->width());
		$this->assertSame(0, $file->dimensions()->height());
		$this->assertFalse($file->isResizable());
	}

	public function testExif(): void
	{
		$file = $this->_image();
		$this->assertInstanceOf(Exif::class, $file->exif());
		// cached object
		$this->assertInstanceOf(Exif::class, $file->exif());
	}

	public function testHeight(): void
	{
		$file = $this->_image();
		$this->assertSame(500, $file->height());
	}

	public function testHtml(): void
	{
		$file = $this->_image();
		$this->assertSame('<img alt="" src="https://foo.bar/cat.jpg">', $file->html());
	}

	public function testHtmlWithoutUrl(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessageIs('Calling File::html() for an image requires that the URL property is not null');
		$file = new File(['root' => static::FIXTURES . '/image/cat.jpg']);
		$file->html();
	}

	public function testImagesize(): void
	{
		$file = $this->_image();
		$this->assertIsArray($file->imagesize());
		$this->assertSame(500, $file->imagesize()[0]);
	}

	public function testImagesizeWithInvalidImage(): void
	{
		$file = $this->_image('invalid.jpg');
		$this->assertFalse($file->imagesize());
	}

	public function testIsLandscape(): void
	{
		$file = $this->_image();
		$this->assertFalse($file->isLandscape());
	}

	public function testIsPortrait(): void
	{
		$file = $this->_image();
		$this->assertFalse($file->isPortrait());
	}

	public function testIsResizable(): void
	{
		$file = $this->_image();
		$this->assertTrue($file->isResizable());

		$file = $this->_image('test.heic');
		$this->assertFalse($file->isResizable());
	}

	public function testIsResizableWithInvalidImage(): void
	{
		$file = $this->_image('invalid.jpg');
		$this->assertFalse($file->isResizable());
	}

	public function testIsSquare(): void
	{
		$file = $this->_image();
		$this->assertTrue($file->isSquare());
	}

	public function testIsViewable(): void
	{
		$file = $this->_image();
		$this->assertTrue($file->isResizable());

		$file = $this->_image('test.heic');
		$this->assertFalse($file->isResizable());
	}

	public function testMatch(): void
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

	public function testMatchOrientationException(): void
	{
		// Make sure i18n files are loaded
		$kirby = kirby();

		$this->expectException(Exception::class);
		$this->expectExceptionCode('error.file.orientation');
		$this->expectExceptionMessageIs('The orientation of the image must be "portrait"');

		$this->_image()->match(['orientation' => 'portrait']);
	}

	public function testOrientation(): void
	{
		$file = $this->_image();
		$this->assertSame('square', $file->orientation());
	}

	public function testRatio(): void
	{
		$image  = $this->_image();
		$this->assertSame(1.0, $image->ratio());
	}

	public function testToArray(): void
	{
		$file = $this->_image();
		$this->assertSame('cat.jpg', $file->toArray()['filename']);
		$this->assertIsArray($file->toArray()['exif']);
		$this->assertIsArray($file->toArray()['dimensions']);
	}

	public function testToString(): void
	{
		$file = $this->_image();
		$expected = '<img alt="" src="https://foo.bar/cat.jpg">';
		$this->assertSame($expected, $file->__toString());
		$this->assertSame($expected, (string)$file);
	}

	public function testWidth(): void
	{
		$file = $this->_image();
		$this->assertSame(500, $file->width());
	}
}
