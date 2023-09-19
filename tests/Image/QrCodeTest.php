<?php

namespace Kirby\Image;

use GdImage;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \Kirby\Image\QrCode
 */
class QrCodeTest extends TestCase
{
	public function testToDataUri()
	{
		$qr = new QrCode('https://getkirby.com');
		$expected = F::read(__DIR__ . '/fixtures/qr/data-uri.txt');
		$this->assertSame($expected, $qr->toDataUri());
	}

	public function testToImage()
	{
		$qr = new QrCode('https://getkirby.com');
		$this->assertInstanceOf(GdImage::class, $qr->toImage());
	}

	public function testToSvg()
	{
		$qr = new QrCode('https://getkirby.com');
		$expected = F::read(__DIR__ . '/fixtures/qr/qr.svg');
		$this->assertSame($expected, $qr->toSvg());
	}

	public function testToSvgColors()
	{
		$qr = new QrCode(
			data: 'https://getkirby.com',
			color: '#ff0000',
			back:  '#00ff00'
		);
		$this->assertStringContainsString('style="fill: #ff0000"/></svg>', $qr->toSvg());
		$this->assertStringContainsString('<rect width="100%" height="100%" style="fill: #00ff00"/>', $qr->toSvg());
	}
}
