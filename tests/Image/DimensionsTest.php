<?php

namespace Kirby\Image;

use Darkroom\Size;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Dimensions::class)]
class DimensionsTest extends TestCase
{
	public function testFrom(): void
	{
		$dimensions = Dimensions::from(new Size(1200, 768));

		$this->assertSame(1200, $dimensions->width());
		$this->assertSame(768, $dimensions->height());
	}

	public function testHeight(): void
	{
		$this->assertSame(768, new Dimensions(1200, 768)->height());
	}

	public function testIsLandscape(): void
	{
		$this->assertTrue(new Dimensions(1200, 768)->isLandscape());
		$this->assertFalse(new Dimensions(768, 1200)->isLandscape());
		$this->assertFalse(new Dimensions(768, 768)->isLandscape());
	}

	public function testIsPortrait(): void
	{
		$this->assertTrue(new Dimensions(768, 1200)->isPortrait());
		$this->assertFalse(new Dimensions(1200, 768)->isPortrait());
		$this->assertFalse(new Dimensions(768, 768)->isPortrait());
	}

	public function testIsSquare(): void
	{
		$this->assertTrue(new Dimensions(768, 768)->isSquare());
		$this->assertFalse(new Dimensions(1200, 768)->isSquare());
	}

	public static function orientationProvider(): array
	{
		return [
			'landscape' => [1200, 768, 'landscape'],
			'portrait'  => [768, 1200, 'portrait'],
			'square'    => [768, 768, 'square'],
			'no size'   => [0, 0, false],
			'no height' => [1200, 0, false],
		];
	}

	#[DataProvider('orientationProvider')]
	public function testOrientation(int $width, int $height, string|false $expected): void
	{
		$this->assertSame($expected, new Dimensions($width, $height)->orientation());
	}

	public function testRatio(): void
	{
		$this->assertSame(1.5625, new Dimensions(1200, 768)->ratio());
		$this->assertSame(0.0, new Dimensions(1200, 0)->ratio());
		$this->assertSame(0.0, new Dimensions(0, 768)->ratio());
	}

	public function testToArray(): void
	{
		$this->assertSame([
			'width'       => 1200,
			'height'      => 768,
			'ratio'       => 1.5625,
			'orientation' => 'landscape',
		], new Dimensions(1200, 768)->toArray());
	}

	public function testToString(): void
	{
		$this->assertSame('1200 × 768', (string)new Dimensions(1200, 768));
	}

	public function testWidth(): void
	{
		$this->assertSame(1200, new Dimensions(1200, 768)->width());
	}
}
