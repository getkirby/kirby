<?php

namespace Kirby\Image\Darkroom;

use Imagick as Image;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

#[CoversClass(Imagick::class)]
class ImagickTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/../fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Image.Darkroom.Imagick';

	public function setUp(): void
	{
		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	protected function call(
		object $object,
		string $method,
		...$args
	): mixed {
		$class  = new ReflectionClass($object);
		$method = $class->getMethod($method);
		return $method->invokeArgs($object, $args);
	}

	public static function orientationProvider(): array
	{
		return [
			['Landscape_0.jpg', Image::ORIENTATION_UNDEFINED],
			['Landscape_1.jpg', Image::ORIENTATION_TOPLEFT],
			['Landscape_2.jpg', Image::ORIENTATION_TOPRIGHT],
			['Landscape_3.jpg', Image::ORIENTATION_BOTTOMRIGHT],
			['Landscape_4.jpg', Image::ORIENTATION_BOTTOMLEFT],
			['Landscape_5.jpg', Image::ORIENTATION_LEFTTOP],
			['Landscape_6.jpg', Image::ORIENTATION_RIGHTTOP],
			['Landscape_7.jpg', Image::ORIENTATION_RIGHTBOTTOM],
			['Landscape_8.jpg', Image::ORIENTATION_LEFTBOTTOM],
			['Portrait_0.jpg', Image::ORIENTATION_UNDEFINED],
			['Portrait_1.jpg', Image::ORIENTATION_TOPLEFT],
			['Portrait_2.jpg', Image::ORIENTATION_TOPRIGHT],
			['Portrait_3.jpg', Image::ORIENTATION_BOTTOMRIGHT],
			['Portrait_4.jpg', Image::ORIENTATION_BOTTOMLEFT],
			['Portrait_5.jpg', Image::ORIENTATION_LEFTTOP],
			['Portrait_6.jpg', Image::ORIENTATION_RIGHTTOP],
			['Portrait_7.jpg', Image::ORIENTATION_RIGHTBOTTOM],
			['Portrait_8.jpg', Image::ORIENTATION_LEFTBOTTOM],
		];
	}

	#[DataProvider('orientationProvider')]
	public function testAutoOrient(
		string $name,
		int $orientation
	): void {
		copy(
			static::FIXTURES . '/orientation/' . $name,
			$file = static::TMP . '/' . $name
		);

		$image   = new Image($file);
		$imagick = new Imagick();

		$this->assertSame($orientation, $image->getImageOrientation());

		// auto orient and assert standard orientation as result
		$result = $this->call($imagick, 'autoOrient', $image);
		$this->assertSame(Image::ORIENTATION_TOPLEFT, $result->getImageOrientation());
	}

	public static function autoOrientMethodProvider(): array
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

	#[DataProvider('autoOrientMethodProvider')]
	public function testAutoOrientMethod(
		int $orientation,
		array $expectedTransformations
	): void {
		$image = $this->createMock(Image::class);
		$image->method('getImageOrientation')->willReturn($orientation);

		foreach ($expectedTransformations as $method) {
			$image->expects($this->once())->method($method);
		}

		$image->expects($this->once())->method('setImageOrientation')
			->with(Image::ORIENTATION_TOPLEFT);

		$imagick = new Imagick();
		$this->call($imagick, 'autoOrient', $image);
	}

	public function testBlur(): void
	{
		$image = $this->createMock(Image::class);

		$image->expects($this->once())
			->method('blurImage')
			->with($this->equalTo(0), $this->equalTo(50));

		$imagick = new Imagick();
		$this->call($imagick, 'blur', $image, ['blur' => 50]);
	}

	public function testCoalesceGif(): void
	{
		copy(
			static::FIXTURES . '/image/animated.gif',
			$file = static::TMP . '/coalesce.gif'
		);

		$image = new Image($file);
		$this->assertSame(3, $image->getNumberImages());

		$imagick = new Imagick();
		$imagick->process($file);

		$image = new Image($file);
		$this->assertSame(3, $image->getNumberImages());
	}

	public function testCoalesceNonGif(): void
	{
		copy(
			static::FIXTURES . '/image/cat.jpg',
			$file = static::TMP . '/non-coalesce.jpg'
		);

		$image = new Image($file);
		$this->assertSame(1, $image->getNumberImages());

		$imagick = new Imagick();
		$imagick->process($file);

		$image = new Image($file);
		$this->assertSame(1, $image->getNumberImages());
	}

	public function testGrayscale(): void
	{
		$image = $this->createMock(Image::class);

		$image->expects($this->once())
			->method('setImageColorspace')
			->with(Image::COLORSPACE_GRAY);

		$imagick = new Imagick();
		$this->call($imagick, 'grayscale', $image, ['grayscale' => true]);
	}

	public function testInterlace(): void
	{
		$image = $this->createMock(Image::class);

		$image->expects($this->once())
			->method('setInterlaceScheme')
			->with(Image::INTERLACE_LINE);

		$imagick = new Imagick();
		$this->call($imagick, 'interlace', $image, ['interlace' => true]);
	}

	public function testProcess(): void
	{
		$imagick = new Imagick();

		copy(
			static::FIXTURES . '/image/cat.jpg',
			$file = static::TMP . '/cat.jpg'
		);

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
			'profiles'     => ['icc', 'icm'],
			'threads'      => 1,
			'sourceWidth'  => 500,
			'sourceHeight' => 500
		], $imagick->process($file));
	}

	public function testQuality(): void
	{
		$image = $this->createMock(Image::class);

		$image->expects($this->once())
			->method('setImageCompressionQuality')
			->with(90);

		$imagick = new Imagick();
		$this->call($imagick, 'quality', $image, ['quality' => 90]);
	}


	public function testResize(): void
	{
		$imagick = new Imagick([
			'crop'   => true,
			'width'  => 200,
			'height' => 150
		]);

		copy(
			static::FIXTURES . '/image/cat.jpg',
			$file = static::TMP . '/resize.jpg'
		);

		$imagick->process($file);
		$image = new Image($file);

		$this->assertEquals(200, $image->getImageWidth());
		$this->assertEquals(150, $image->getImageHeight());
	}

	public function testResizeWithoutCrop(): void
	{
		$imagick = new Imagick([
			'crop'   => false,
			'width'  => 200
		]);

		copy(
			static::FIXTURES . '/image/cat.jpg',
			$file = static::TMP . '/resize.jpg'
		);

		$imagick->process($file);
		$image = new Image($file);

		$this->assertEquals(200, $image->getImageWidth());
		$this->assertEquals(200, $image->getImageHeight());
	}

	public function testResizeWithFocusPoint(): void
	{
		$imagick = new Imagick([
			'crop'   => '25% 0%',
			'width'  => 200,
			'height' => 150
		]);

		copy(
			static::FIXTURES . '/image/cat.jpg',
			$file = static::TMP . '/resize.jpg'
		);

		$imagick->process($file);
		$image = new Image($file);

		$this->assertEquals(200, $image->getImageWidth());
		$this->assertEquals(150, $image->getImageHeight());
	}

	public function testSaveWithFormat(): void
	{
		$imagick = new Imagick(['format' => 'webp']);

		copy(
			static::FIXTURES . '/image/cat.jpg',
			$file = static::TMP . '/cat.jpg'
		);

		$this->assertFalse(F::exists($webp = static::TMP . '/cat.webp'));
		$imagick->process($file);
		$this->assertTrue(F::exists($webp));
	}

	public function testSharpen(): void
	{
		$image = $this->createMock(Image::class);

		$image->expects($this->once())
			->method('sharpenImage')
			->with(0, 0.5);

		$imagick = new Imagick();
		$this->call($imagick, 'sharpen', $image, ['sharpen' => 50]);
	}

	public static function stripProvider(): array
	{
		return [
			['cat.jpg', null, false],
			['cat.jpg', null, true],
			['onigiri-adobe-rgb-gps.jpg', 'Adobe RGB (1998)', false],
			['onigiri-adobe-rgb-gps.jpg', 'Adobe RGB (1998)', true],
			['onigiri-adobe-rgb-gps.webp', 'Adobe RGB (1998)', false],
			['onigiri-adobe-rgb-gps.webp', 'Adobe RGB (1998)', true],
			['png-adobe-rgb-gps.png', 'Adobe RGB (1998)', false],
			['png-adobe-rgb-gps.png', 'Adobe RGB (1998)', true],
			['png-srgb-gps.png', 'sRGB IEC61966-2.1', false],
			['png-srgb-gps.png', 'sRGB IEC61966-2.1', true],
		];
	}

	#[DataProvider('stripProvider')]
	public function testStrip(
		string $name,
		string|null $profile,
		bool $crop
	): void {
		copy(
			static::FIXTURES . '/image/' . $name,
			$file = static::TMP . '/' . $name
		);

		$imagick = new Imagick();
		$image   = new Image($file);
		$before  = $image->getImageProfiles();
		$result  = $this->call($imagick, 'strip', $image, ['profiles' => ['icc']]);
		$after   = $result->getImageProfiles();

		if (isset($before['icc']) === true) {
			$this->assertSame($before['icc'], $after['icc']);
			$this->assertSame(['icc'], array_keys($after));

			// if all profiles are to be removed
			$result  = $this->call($imagick, 'strip', $image, []);
			$after   = $result->getImageProfiles();
			$this->assertSame([], array_keys($after));

		} else {
			$this->assertSame([], array_keys($after));
		}
	}

	#[DataProvider('stripProvider')]
	public function testStripWhenProcessing(
		string $name,
		string|null $profile,
		bool $crop
	): void {

		copy(
			static::FIXTURES . '/image/' . $name,
			$file = static::TMP . '/' . $name
		);

		$imagick = new Imagick([
			'crop'  => $crop,
			'width' => 250, // do some arbitrary transformation
		]);

		// test if profile has been kept
		// errors have to be redirected to /dev/null,
		// otherwise they would be printed to stdout by Imagick
		$command = 'identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null';
		$before  = shell_exec($command);
		$this->assertSame($profile, $before);

		$imagick->process($file);
		$after = shell_exec($command);
		$this->assertSame($profile, $after);

		// ensure that other metadata has been stripped
		$meta = shell_exec('identify -verbose ' . escapeshellarg($file));
		$this->assertStringNotContainsString('photoshop:CaptionWriter', $meta);
		$this->assertStringNotContainsString('GPS', $meta);
	}
}
