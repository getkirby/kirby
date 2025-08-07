<?php

namespace Kirby\Image;

use GdImage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(QrCode::class)]
class QrCodeTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/qr';
	public const TMP      = KIRBY_TMP_DIR . '/Image.QrCode';

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testToDataUri(): void
	{
		$qr = new QrCode('12345678');
		$expected = F::read(static::FIXTURES . '/num.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('ABCDEF12345');
		$expected = F::read(static::FIXTURES . '/alphanum.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('https://getkirby.com');
		$expected = F::read(static::FIXTURES . '/url.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('🥳🐨🏳️‍🌈');
		$expected = F::read(static::FIXTURES . '/emoji.txt');
		$this->assertSame($expected, $qr->toDataUri());

		$qr = new QrCode('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
		$expected = F::read(static::FIXTURES . '/lorem.txt');
		$this->assertSame($expected, $qr->toDataUri());
	}

	public function testToImage(): void
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage();
		$data     = $this->imageContent($image);
		$expected = F::read(static::FIXTURES . '/image.png');

		$this->assertInstanceOf(GdImage::class, $image);
		$this->assertSame($expected, $data);
	}

	public function testToImageSize(): void
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage(750);
		$data     = $this->imageContent($image);
		$expected = F::read(static::FIXTURES . '/image-size.png');
		$this->assertSame($expected, $data);
	}

	public function testToImageColors(): void
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage(null, '#00ff00', '#0000ff');
		$data     = $this->imageContent($image);
		$expected = F::read(static::FIXTURES . '/image-colors.png');
		$this->assertSame($expected, $data);
	}

	public function testToImageBorder(): void
	{
		$qr       = new QrCode('https://getkirby.com');
		$image    = $qr->toImage(null, '#000000', '#ffffff', 0);
		$data     = $this->imageContent($image);
		$expected = F::read(static::FIXTURES . '/image-border.png');
		$this->assertSame($expected, $data);
	}

	public function testToSvg(): void
	{
		$qr = new QrCode('12345678');
		$expected = F::read(static::FIXTURES . '/num.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('ABCDEF12345');
		$expected = F::read(static::FIXTURES . '/alphanum.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('https://getkirby.com');
		$expected = F::read(static::FIXTURES . '/url.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('🥳🐨🏳️‍🌈');
		$expected = F::read(static::FIXTURES . '/emoji.svg');
		$this->assertSame($expected, $qr->toSvg());

		$qr = new QrCode('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.');
		$expected = F::read(static::FIXTURES . '/lorem.svg');
		$this->assertSame($expected, $qr->toSvg());
	}

	public function testToSvgColors(): void
	{
		$qr = new QrCode('https://getkirby.com');
		$svg = $qr->toSvg(
			color: '#ff0000',
			back:  '#00ff00'
		);

		$this->assertStringContainsString('fill="#ff0000"/></svg>', $svg);
		$this->assertStringContainsString('<rect width="100%" height="100%" fill="#00ff00"/>', $svg);
	}

	public function testToString(): void
	{
		$qr = new QrCode('https://getkirby.com');
		$this->assertSame($qr->toSvg(), (string)$qr);
	}

	public function testWrite(): void
	{
		Dir::make(static::TMP);

		$qr = new QrCode('https://getkirby.com');

		$qr->write($file = static::TMP . '/test.svg');
		$this->assertFileExists($file);
		$this->assertFileEquals(static::FIXTURES . '/test.svg', $file);

		$qr->write($file = static::TMP . '/test.png');
		$this->assertFileExists($file);
		$this->assertFileEquals(static::FIXTURES . '/test.png', $file);

		// TODO: We are currently skipping this test because of an
		// unexplainable, failed comparison in CI. We should replace
		// the binary comparison with a dimension comparison,
		// as suggested by Lukas.
		//
		// $qr->write($file = static::TMP . '/test.gif');
		// $this->assertFileExists($file);
		// $this->assertFileEquals(static::FIXTURES . '/test.gif', $file);

		$qr->write($file = static::TMP . '/test.webp');
		$this->assertFileExists($file);
		$this->assertFileEquals(static::FIXTURES . '/test.webp', $file);

		// test JPEG by comparing the output dynamically to avoid issues
		// with different libraries/library versions in CI
		$fixture      = static::FIXTURES . '/test.jpg';
		$expectedJpeg = $this->imageContent(imagecreatefromjpeg($fixture));

		$qr->write($file = static::TMP . '/test.jpg');
		$this->assertFileExists($file);
		$actualJpeg = $this->imageContent(imagecreatefromjpeg($file));
		$this->assertSame($expectedJpeg, $actualJpeg);

		$qr->write($file = static::TMP . '/test.jpeg');
		$this->assertFileExists($file);
		$actualJpeg = $this->imageContent(imagecreatefromjpeg($file));
		$this->assertSame($expectedJpeg, $actualJpeg);
	}

	public function testWriteInvalidFormat(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot write QR code as docx');

		$qr = new QrCode('https://getkirby.com');
		$qr->write('test.docx');
	}

	protected function imageContent(GdImage $image): string
	{
		ob_start();
		imagepng($image);
		imagedestroy($image);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}
}
