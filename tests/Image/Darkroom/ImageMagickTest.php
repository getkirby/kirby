<?php

namespace Kirby\Image\Darkroom;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use Kirby\Toolkit\F;
use ReflectionMethod;

/**
 * @coversDefaultClass \Kirby\Image\Darkroom\ImageMagick
 */
class ImageMagickTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/../fixtures/image';
	public const TMP      = KIRBY_TMP_DIR . '/Image.Darkroom.ImageMagick';

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
		$im = new ImageMagick();

		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');

		$this->assertSame([
			'autoOrient' => true,
			'blur' => false,
			'crop' => false,
			'format' => null,
			'grayscale' => false,
			'height' => 500,
			'quality' => 90,
			'scaleHeight' => 1.0,
			'scaleWidth' => 1.0,
			'sharpen' => null,
			'width' => 500,
			'bin' => 'convert',
			'interlace' => false,
			'threads' => 1,
			'sourceWidth' => 500,
			'sourceHeight' => 500
		], $im->process($file));
	}

	/**
	 * @covers ::sharpen
	 */
	public function testSharpen()
	{
		$im = new ImageMagick();

		$method = new ReflectionMethod(get_class($im), 'sharpen');
		$method->setAccessible(true);

		$result = $method->invoke($im, '', [
			'sharpen' => 50
		]);

		$this->assertSame("-sharpen '0x0.5'", $result);
	}

	/**
	 * @covers ::sharpen
	 */
	public function testSharpenWithoutValue()
	{
		$im = new ImageMagick();

		$method = new ReflectionMethod(get_class($im), 'sharpen');
		$method->setAccessible(true);

		$result = $method->invoke($im, '', [
			'sharpen' => null
		]);

		$this->assertNull($result);
	}

	/**
	 * @covers ::save
	 */
	public function testSaveWithFormat()
	{
		$im = new ImageMagick(['format' => 'webp']);

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
		$im = new ImageMagick([
			'bin' => 'convert',
			'crop' => $crop,
			'width' => 250, // do some arbitrary transformation
		]);

		copy(static::FIXTURES . '/' . $basename, $file = static::TMP . '/' . $basename);

		// test if profile has been kept
		// errors have to be redirected to /dev/null, otherwise they would be printed to stdout by ImageMagick
		$originalProfile = shell_exec('identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null');
		$im->process($file);
		$profile = shell_exec('identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null');

		if (F::extension($basename) === 'png') {
			// ensure that the profile has been stripped from PNG files, because
			// ImageMagick cannot keep it while stripping all other metadata
			// (tested with ImageMagick 7.0.11-14 Q16 x86_64 2021-05-31)
			$this->assertNull($profile);
		} else {
			// ensure that the profile has been kept for all other file types
			$this->assertSame($originalProfile, $profile);
		}

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
}
