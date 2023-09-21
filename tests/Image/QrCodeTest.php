<?php

namespace Kirby\Image;

use GdImage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \Kirby\Image\QrCode
 */
class QrCodeTest extends TestCase
{
	protected function imageContent(GdImage $image): string
	{
		ob_start();
		imagepng($image);
		imagedestroy($image);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	public function testToDataUri()
	{
		$qr = new QrCode('12345678');
		$expected = F::read(__DIR__ . '/fixtures/qr/num.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('ABCDEF12345');
		$expected = F::read(__DIR__ . '/fixtures/qr/alphanum.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('https://getkirby.com');
		$expected = F::read(__DIR__ . '/fixtures/qr/url.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('🥳🐨🏳️‍🌈');
		$expected = F::read(__DIR__ . '/fixtures/qr/emoji.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
		$expected = F::read(__DIR__ . '/fixtures/qr/lorem.txt');
		$this->assertSame($expected, $qr->toDataUri());
	}

	public function testToImage()
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage();
		$data     = $this->imageContent($image);
		$expected = F::read(__DIR__ . '/fixtures/qr/image.png');

		$this->assertInstanceOf(GdImage::class, $image);
		$this->assertSame($expected, $data);
	}

	public function testToImageSize()
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage(750);
		$data     = $this->imageContent($image);
		$expected = F::read(__DIR__ . '/fixtures/qr/image-size.png');
		$this->assertSame($expected, $data);
	}

	public function testToImageColors()
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage(null, '#00ff00', '#0000ff');
		$data     = $this->imageContent($image);
		$expected = F::read(__DIR__ . '/fixtures/qr/image-colors.png');
		$this->assertSame($expected, $data);
	}

	public function testToSvg()
	{
		$qr = new QrCode('12345678');
		$expected = F::read(__DIR__ . '/fixtures/qr/num.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('ABCDEF12345');
		$expected = F::read(__DIR__ . '/fixtures/qr/alphanum.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('https://getkirby.com');
		$expected = F::read(__DIR__ . '/fixtures/qr/url.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('🥳🐨🏳️‍🌈');
		$expected = F::read(__DIR__ . '/fixtures/qr/emoji.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
		$expected = F::read(__DIR__ . '/fixtures/qr/lorem.svg');
		$this->assertSame($expected, $qr->toSvg());
	}

	public function testToSvgColors()
	{
		$qr = new QrCode('https://getkirby.com');
		$svg = $qr->toSvg(
			color: '#ff0000',
			back:  '#00ff00'
		);

		$this->assertStringContainsString('fill="#ff0000"/></svg>', $svg);
		$this->assertStringContainsString('<rect width="100%" height="100%" fill="#00ff00"/>', $svg);
	}

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$qr = new QrCode('https://getkirby.com');
		$this->assertSame($qr->toSvg(), (string)$qr);
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		Dir::make($dir = __DIR__ . '/tmp');

		$qr = new QrCode('https://getkirby.com');

		$qr->write($file = $dir . '/test.gif');
		$this->assertFileExists($file);
		$this->assertSame('image/gif', mime_content_type($file));
		$this->assertSame(F::read(__DIR__ . '/fixtures/qr/test.gif'), F::read($file));

		$qr->write($file = $dir . '/test.jpg');
		$this->assertFileExists($file);
		$this->assertSame('image/jpeg', mime_content_type($file));
		$this->assertSame(F::read(__DIR__ . '/fixtures/qr/test.jpg'), F::read($file));

		$qr->write($file = $dir . '/test.jpeg');
		$this->assertFileExists($file);
		$this->assertSame('image/jpeg', mime_content_type($file));
		$this->assertSame(F::read(__DIR__ . '/fixtures/qr/test.jpeg'), F::read($file));

		$qr->write($file = $dir . '/test.png');
		$this->assertFileExists($file);
		$this->assertSame('image/png', mime_content_type($file));
		$this->assertSame(F::read(__DIR__ . '/fixtures/qr/test.png'), F::read($file));

		$qr->write($file = $dir . '/test.svg');
		$this->assertFileExists($file);
		$this->assertSame('image/svg+xml', mime_content_type($file));
		$this->assertSame(F::read(__DIR__ . '/fixtures/qr/test.svg'), F::read($file));

		$qr->write($file = $dir . '/test.webp');
		$this->assertFileExists($file);
		$this->assertSame('image/webp', mime_content_type($file));
		$this->assertSame(F::read(__DIR__ . '/fixtures/qr/test.webp'), F::read($file));

		Dir::remove($dir);
	}

	/**
	 * @covers ::write
	 */
	public function testWriteInvalidFormat()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot write QR code as pdf');

		$qr = new QrCode('https://getkirby.com');
		$qr->write('test.pdf');
	}
}
