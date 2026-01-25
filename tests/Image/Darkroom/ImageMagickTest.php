<?php

namespace Kirby\Image\Darkroom;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F as FilesystemF;
use Kirby\TestCase;
use Kirby\Toolkit\F;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;

#[CoversClass(ImageMagick::class)]
class ImageMagickTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/../fixtures/image';
	public const string TMP      = KIRBY_TMP_DIR . '/Image.Darkroom.ImageMagick';

	public function setUp(): void
	{
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testProcess(): void
	{
		$im = new ImageMagick();

		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');

		$this->assertSame([
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

	public function testSharpen(): void
	{
		$im = new ImageMagick();

		$method = new ReflectionMethod($im::class, 'sharpen');

		$result = $method->invoke($im, '', [
			'sharpen' => 50
		]);

		$this->assertSame("-sharpen '0x0.5'", $result);
	}

	public function testSharpenWithoutValue(): void
	{
		$im = new ImageMagick();

		$method = new ReflectionMethod($im::class, 'sharpen');

		$result = $method->invoke($im, '', [
			'sharpen' => null
		]);

		$this->assertNull($result);
	}

	public function testSaveWithFormat(): void
	{
		$im = new ImageMagick(['format' => 'webp']);

		copy(
			static::FIXTURES . '/cat.jpg',
			$file = static::TMP . '/cat.jpg'
		);

		$format = shell_exec('identify -format "%m" ' . escapeshellarg($file) . ' 2>/dev/null');
		$this->assertSame('JPEG', $format);

		$im->process($file);
		$this->assertTrue(FilesystemF::exists($file));
		$format = shell_exec('identify -format "%m" ' . escapeshellarg($file) . ' 2>/dev/null');
		$this->assertSame('WEBP', $format);
	}

	#[DataProvider('keepColorProfileStripMetaProvider')]
	public function testKeepColorProfileStripMeta(string $basename, bool $crop): void
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
