<?php

namespace Kirby\Image;

use GdImage;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \Kirby\Image\QrCode
 */
class QrCodeTest extends TestCase
{
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
		$qr = new QrCode('https://getkirby.com');
		$this->assertInstanceOf(GdImage::class, $qr->toImage());
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
		$qr = new QrCode(
			data: 'https://getkirby.com',
			color: '#ff0000',
			back:  '#00ff00'
		);
		$this->assertStringContainsString('fill="#ff0000"/></svg>', $qr->toSvg());
		$this->assertStringContainsString('<rect width="100%" height="100%" fill="#00ff00"/>', $qr->toSvg());
	}

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$qr = new QrCode('https://getkirby.com');
		$this->assertSame((string)$qr, $qr->toSvg());
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

		$qr->write($file = $dir . '/test.jpg');
		$this->assertFileExists($file);

		$qr->write($file = $dir . '/test.jpeg');
		$this->assertFileExists($file);

		$qr->write($file = $dir . '/test.png');
		$this->assertFileExists($file);

		$qr->write($file = $dir . '/test.svg');
		$this->assertFileExists($file);

		$qr->write($file = $dir . '/test.webp');
		$this->assertFileExists($file);

		Dir::remove($dir);
	}
}
