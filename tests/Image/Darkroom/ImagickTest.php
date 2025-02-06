<?php

namespace Kirby\Image\Darkroom;

use Imagick as Image;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \Kirby\Image\Darkroom\Imagick
 */
class ImagickTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/../fixtures/image';
	public const TMP      = KIRBY_TMP_DIR . '/Image.Darkroom.Imagick';

	public function setUp(): void
	{
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testProcess()
	{
		$im = new Imagick();

		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');

		$this->assertSame([
			'blur'         => false,
			'crop'         => false,
			'format'       => null,
			'grayscale'    => false,
			'height'       => 500,
			'quality'      => 90,
			'scaleHeight'  => 1.0,
			'scaleWidth'   => 1.0,
			'sharpen'      => null,
			'width'        => 500,
			'interlace'    => false,
			'threads'      => 1,
			'sourceWidth'  => 500,
			'sourceHeight' => 500
		], $im->process($file));
	}

	/**
	 * @covers ::save
	 */
	public function testSaveWithFormat()
	{
		$im = new Imagick(['format' => 'webp']);

		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');
		$this->assertFalse(F::exists($webp = static::TMP . '/cat.webp'));
		$im->process($file);
		$this->assertTrue(F::exists($webp));
	}

	/**
	 * @dataProvider keepColorProfileStripMetaProvider
	 */
	public function testKeepColorProfileStripMeta(string $basename, bool $crop)
	{
		$im = new Imagick([
			'crop'  => $crop,
			'width' => 250, // do some arbitrary transformation
		]);

		copy(
			static::FIXTURES . '/' . $basename,
			$file = static::TMP . '/' . $basename
		);

		// test if profile has been kept
		// errors have to be redirected to /dev/null,
		// otherwise they would be printed to stdout by Imagick
		$command = 'identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null';
		$before  = shell_exec($command);
		$im->process($file);
		$after = shell_exec($command);
		$this->assertSame($before, $after);

		// ensure that other metadata has been stripped
		$meta = shell_exec('identify -verbose ' . escapeshellarg($file));
		$this->assertStringNotContainsString('photoshop:CaptionWriter', $meta);
		$this->assertStringNotContainsString('GPS', $meta);
	}

	public static function keepColorProfileStripMetaProvider(): array
	{
		return [
			['cat.jpg', false],
			['cat.jpg', true],
			['onigiri-adobe-rgb-gps.jpg', false],
			['onigiri-adobe-rgb-gps.jpg', true],
			['onigiri-adobe-rgb-gps.webp', false],
			['onigiri-adobe-rgb-gps.webp', true],
			['png-adobe-rgb-gps.png', false],
			['png-adobe-rgb-gps.png', true],
			['png-srgb-gps.png', false],
			['png-srgb-gps.png', true],
		];
	}

	/**
	 * @dataProvider autoOrientProvider
	 */
	public function testAutoOrient(int $orientation, array $expectedTransformations)
	{
		$image = $this->createMock(Image::class);
		$image->method('getImageOrientation')->willReturn($orientation);

		foreach ($expectedTransformations as $method) {
			$image->expects($this->once())->method($method);
		}

		$image->expects($this->once())->method('setImageOrientation')
			  ->with(Image::ORIENTATION_TOPLEFT);

		$imagick = new Imagick();
		$result  = (new ReflectionClass($imagick))->getMethod('autoOrient');
		$result->invoke($imagick, $image);
	}

	public static function autoOrientProvider(): array
	{
		return [
			'No change'         => [Image::ORIENTATION_TOPLEFT, []],
			'Flop'              => [Image::ORIENTATION_TOPRIGHT, ['flopImage']],
			'Rotate 180'        => [Image::ORIENTATION_BOTTOMRIGHT, ['rotateImage']],
			'Flop + Rotate 180' => [Image::ORIENTATION_BOTTOMLEFT, ['flopImage', 'rotateImage']],
			'Flop + Rotate -90' => [Image::ORIENTATION_LEFTTOP, ['flopImage', 'rotateImage']],
			'Rotate 90'         => [Image::ORIENTATION_RIGHTTOP, ['rotateImage']],
			'Flop + Rotate 90'  => [Image::ORIENTATION_RIGHTBOTTOM, ['flopImage', 'rotateImage']],
			'Rotate -90'        => [Image::ORIENTATION_LEFTBOTTOM, ['rotateImage']],
		];
	}

	public function testSharpen()
	{
		$image = $this->createMock(Image::class);

		$image->expects($this->once())
			  ->method('sharpenImage')
			  ->with($this->equalTo(0), $this->equalTo(0.5));

		$imagick    = new Imagick();
		$reflection = new ReflectionClass($imagick);
		$method     = $reflection->getMethod('sharpen');
		$method->invoke($imagick, $image, ['sharpen' => 50]);
	}

	public function testInterlace()
	{
		$im = new Imagick();

		copy(
			static::FIXTURES . '/cat.jpg',
			$file = static::TMP . '/interlace.jpg'
		);

		$im->process($file);

		$processedImage = new Image($file);
		$this->assertEquals(Image::INTERLACE_NO, $processedImage->getInterlaceScheme());
	}

	public function testInterlaceEnabled()
	{
		$im = new Imagick(['interlace' => true]);

		copy(
			static::FIXTURES . '/cat.jpg',
			$file = static::TMP . '/interlace.jpg'
		);

		$im->process($file);

		$processedImage = new Image($file);
		$this->assertEquals(Image::INTERLACE_LINE, $processedImage->getInterlaceScheme());
	}

	public function testGrayscale()
	{
		$im = new Imagick(['grayscale' => true]);

		copy(
			static::FIXTURES . '/cat.jpg',
			$file = static::TMP . '/grayscale.jpg'
		);

		$im->process($file);

		$processedImage = new Image($file);
		$this->assertEquals(Image::COLORSPACE_GRAY, $processedImage->getImageColorspace());
	}

	public function testCoalesce()
	{
		copy(
			static::FIXTURES . '/animated.gif',
			$file = static::TMP . '/coalesce.gif'
		);

		$image = new Image($file);
		$this->assertSame(3, $image->getNumberImages());

		$im = new Imagick();
		$im->process($file);

		$image = new Image($file);
		$this->assertSame(3, $image->getNumberImages());
	}

	public function testResize()
	{
		$im = new Imagick([
			'crop'   => true,
			'width'  => 200,
			'height' => 150
		]);

		copy(
			static::FIXTURES . '/cat.jpg',
			$file = static::TMP . '/resize.jpg'
		);

		$im->process($file);
		$image = new Image($file);

		$this->assertEquals(200, $image->getImageWidth());
		$this->assertEquals(150, $image->getImageHeight());
	}

	/**
	 * @dataProvider resizeGravityProvider
	 */
	public function testResizeGravity(string $crop, int $gravity)
	{
		$image = $this->createMock(Image::class);
		$image->method('getImageGravity')->willReturn($gravity);
		$image->expects($this->once())->method('setGravity')->with($gravity);

		$imagick = new Imagick();
		$result  = (new ReflectionClass($imagick))->getMethod('resize');
		$result->invoke($imagick, $image, ['crop' => $crop, 'width'  => 200, 'height' => 150]);
	}

	public static function resizeGravityProvider(): array
	{
		return [
			['top left', Image::GRAVITY_NORTHWEST],
			['top', Image::GRAVITY_NORTH],
			['top right', Image::GRAVITY_NORTHEAST],
			['left', Image::GRAVITY_WEST],
			['right', Image::GRAVITY_EAST],
			['bottom left', Image::GRAVITY_SOUTHWEST],
			['bottom', Image::GRAVITY_SOUTH],
			['bottom right', Image::GRAVITY_SOUTHEAST],
			['center', Image::GRAVITY_CENTER]
		];
	}
}
