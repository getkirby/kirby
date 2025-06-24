<?php

namespace Kirby\Image;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Dimensions
 */
class DimensionsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	/**
	 * @covers ::__construct
	 * @covers ::width
	 * @covers ::height
	 */
	public function testDimensions()
	{
		$dimensions = new Dimensions(1200, 768);
		$this->assertSame(1200, $dimensions->width());
		$this->assertSame(768, $dimensions->height());
	}

	/**
	 * @covers ::crop
	 */
	public function testCrop()
	{
		$dimensions = new Dimensions(1200, 768);
		$dimensions->crop(1000, 500);
		$this->assertSame(1000, $dimensions->width());
		$this->assertSame(500, $dimensions->height());

		$dimensions = new Dimensions(1200, 768);
		$dimensions->crop(500);
		$this->assertSame(500, $dimensions->width());
		$this->assertSame(500, $dimensions->height());
	}

	/**
	 * @covers ::fit
	 */
	public function testFit()
	{
		// zero dimensions
		$dimensions = new Dimensions(0, 0);
		$dimensions->fit(500);
		$this->assertSame(500, $dimensions->width());
		$this->assertSame(500, $dimensions->height());

		// wider than tall
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fit(500);
		$this->assertSame(500, $dimensions->width());
		$this->assertSame(320, $dimensions->height());

		// taller than wide
		$dimensions = new Dimensions(768, 1200);
		$dimensions->fit(500);
		$this->assertSame(320, $dimensions->width());
		$this->assertSame(500, $dimensions->height());

		// width = height but bigger than box
		$dimensions = new Dimensions(1200, 1200);
		$dimensions->fit(500);
		$this->assertSame(500, $dimensions->width());
		$this->assertSame(500, $dimensions->height());

		// smaller than new size
		$dimensions = new Dimensions(300, 200);
		$dimensions->fit(500);
		$this->assertSame(300, $dimensions->width());
		$this->assertSame(200, $dimensions->height());
	}

	/**
	 * @covers ::fit
	 */
	public function testFitForce()
	{
		// wider than tall
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fit(2000, true);
		$this->assertSame(2000, $dimensions->width());
		$this->assertSame(1280, $dimensions->height());

		// taller than wide
		$dimensions = new Dimensions(768, 1200);
		$dimensions->fit(2000, true);
		$this->assertSame(1280, $dimensions->width());
		$this->assertSame(2000, $dimensions->height());
	}

	/**
	 * @covers ::fitWidth
	 */
	public function testFitWidth()
	{
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitWidth(0);
		$this->assertSame(1200, $dimensions->width());
		$this->assertSame(768, $dimensions->height());

		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitWidth(500);
		$this->assertSame(500, $dimensions->width());
		$this->assertSame(320, $dimensions->height());

		// no upscale
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitWidth(2000);
		$this->assertSame(1200, $dimensions->width());
		$this->assertSame(768, $dimensions->height());

		// force upscale
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitWidth(2000, true);
		$this->assertSame(2000, $dimensions->width());
		$this->assertSame(1280, $dimensions->height());
	}

	/**
	 * @covers ::fitHeight
	 */
	public function testFitHeight()
	{
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitHeight(0);
		$this->assertSame(1200, $dimensions->width());
		$this->assertSame(768, $dimensions->height());

		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitHeight(500);
		$this->assertSame(781, $dimensions->width());
		$this->assertSame(500, $dimensions->height());

		// no upscale
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitHeight(2000);
		$this->assertSame(1200, $dimensions->width());
		$this->assertSame(768, $dimensions->height());

		// force upscale
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitHeight(2000, true);
		$this->assertSame(3125, $dimensions->width());
		$this->assertSame(2000, $dimensions->height());
	}

	/**
	 * @covers ::fitWidthAndHeight
	 */
	public function testFitWidthAndHeight()
	{
		$dimensions = new Dimensions(1200, 768);
		$dimensions->fitWidthAndHeight(1000, 500);
		$this->assertSame(781, $dimensions->width());
		$this->assertSame(500, $dimensions->height());

		$dimensions = new Dimensions(768, 1200);
		$dimensions->fitWidthAndHeight(500, 1000);
		$this->assertSame(500, $dimensions->width());
		$this->assertSame(781, $dimensions->height());
	}

	/**
	 * @covers ::forImage
	 */
	public function testForImage()
	{
		$image = new Image([
			'root' => __DIR__ . '/fixtures/image/onigiri-adobe-rgb-gps.jpg'
		]);
		$dimensions = Dimensions::forImage($image);
		$this->assertSame(600, $dimensions->width());
		$this->assertSame(400, $dimensions->height());

		$image = new Image([
			'root' => __DIR__ . '/fixtures/image/onigiri-adobe-rgb-gps.webp'
		]);

		$dimensions = Dimensions::forImage($image);
		$this->assertSame(600, $dimensions->width());
		$this->assertSame(400, $dimensions->height());

		if (version_compare(phpversion(), '8.2.0') >= 0) {
			$image = new Image([
				'root' => __DIR__ . '/fixtures/image/onigiri-adobe-rgb-gps.avif'
			]);

			$dimensions = Dimensions::forImage($image);
			$this->assertSame(600, $dimensions->width());
			$this->assertSame(400, $dimensions->height());
		}
	}

	public static function imageOrientationProvider(): array
	{
		return [
			['Landscape_0.jpg', 1800, 1200],
			['Landscape_1.jpg', 1800, 1200],
			['Landscape_2.jpg', 1800, 1200],
			['Landscape_3.jpg', 1800, 1200],
			['Landscape_4.jpg', 1800, 1200],
			['Landscape_5.jpg', 1800, 1200],
			['Landscape_6.jpg', 1800, 1200],
			['Landscape_7.jpg', 1800, 1200],
			['Landscape_8.jpg', 1800, 1200],
			['Portrait_0.jpg', 1200, 1800],
			['Portrait_1.jpg', 1200, 1800],
			['Portrait_2.jpg', 1200, 1800],
			['Portrait_3.jpg', 1200, 1800],
			['Portrait_4.jpg', 1200, 1800],
			['Portrait_5.jpg', 1200, 1800],
			['Portrait_6.jpg', 1200, 1800],
			['Portrait_7.jpg', 1200, 1800],
			['Portrait_8.jpg', 1200, 1800]
		];
	}

	/**
	 * @dataProvider imageOrientationProvider
	 * @covers ::forImage
	 */
	public function testForImageOrientation(
		string $filename,
		int $width,
		int $height
	) {
		$image = new Image([
			'root' => __DIR__ . '/fixtures/orientation/' . $filename
		]);

		$dimensions = Dimensions::forImage($image);
		$this->assertSame($width, $dimensions->width());
		$this->assertSame($height, $dimensions->height());
	}

	/**
	 * @covers ::forSvg
	 */
	public function testForSvg()
	{
		$dimensions = Dimensions::forSvg(static::FIXTURES . '/dimensions/circle.svg');
		$this->assertSame(50, $dimensions->width());
		$this->assertSame(50, $dimensions->height());

		$dimensions = Dimensions::forSvg(static::FIXTURES . '/dimensions/circle-abs.svg');
		$this->assertSame(35, $dimensions->width());
		$this->assertSame(35, $dimensions->height());

		$dimensions = Dimensions::forSvg(static::FIXTURES . '/dimensions/circle-offset.svg');
		$this->assertSame(40, $dimensions->width());
		$this->assertSame(25, $dimensions->height());
	}

	/**
	 * @covers ::orientation
	 */
	public function testOrientation()
	{
		$dimensions = new Dimensions(1200, 768);
		$this->assertSame('landscape', $dimensions->orientation());

		$dimensions = new Dimensions(768, 1200);
		$this->assertSame('portrait', $dimensions->orientation());

		$dimensions = new Dimensions(1200, 1200);
		$this->assertSame('square', $dimensions->orientation());
		$this->assertTrue($dimensions->square());

		$dimensions = new Dimensions(0, 0);
		$this->assertFalse($dimensions->orientation());
	}

	/**
	 * @covers ::ratio
	 */
	public function testRatio()
	{
		$dimensions = new Dimensions(1200, 768);
		$this->assertSame(1.5625, $dimensions->ratio());

		$dimensions = new Dimensions(768, 1200);
		$this->assertSame(0.64, $dimensions->ratio());

		$dimensions = new Dimensions(0, 0);
		$this->assertSame(0.0, $dimensions->ratio());
	}

	/**
	 * @covers ::resize
	 */
	public function testResize()
	{
		$dimensions = new Dimensions(1200, 768);
		$dimensions->resize(2000, 800, true);
		$this->assertSame(1250, $dimensions->width());
		$this->assertSame(800, $dimensions->height());
	}

	/**
	 * @covers ::toArray
	 * @covers ::__debugInfo
	 */
	public function testToArray()
	{
		$dimensions = new Dimensions(1200, 768);
		$array = [
			'width'       => 1200,
			'height'      => 768,
			'ratio'       => 1.5625,
			'orientation' => 'landscape'
		];
		$this->assertSame($array, $dimensions->toArray());
		$this->assertSame($array, $dimensions->__debugInfo());
	}

	/**
	 * @covers ::__toString
	 */
	public function testToString()
	{
		$dimensions = new Dimensions(1200, 768);
		$this->assertSame('1200 × 768', (string)$dimensions);
	}
}
