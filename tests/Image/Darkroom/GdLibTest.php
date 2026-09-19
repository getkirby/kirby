<?php

namespace Kirby\Image\Darkroom;

use claviska\SimpleImage;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;

class SimpleImageMock extends SimpleImage
{
	public int $sharpen = 50;
	public array $crops = [];

	public function crop(int|float $x1, int|float $y1, int|float $x2, int|float $y2): static
	{
		$this->crops[] = [$x1, $y1, $x2, $y2];
		return parent::crop($x1, $y1, $x2, $y2);
	}

	public function sharpen(int $amount = 50): static
	{
		$this->sharpen = $amount;
		return $this;
	}
}


#[CoversClass(GdLib::class)]
class GdLibTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/../fixtures/image';
	public const string TMP      = KIRBY_TMP_DIR . '/Image.Darkroom.GdLib';

	protected function setUp(): void
	{
		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testProcess(): void
	{
		$gd = new GdLib();

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
			'sourceWidth' => 500,
			'sourceHeight' => 500,
		], $gd->process($file));
	}

	public function testProcessWithFormat(): void
	{
		$gd = new GdLib(['format' => 'webp']);
		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');
		$this->assertSame('webp', $gd->process($file)['format']);
	}

	public function testResize(): void
	{
		$gd = new GdLib([
			'crop'   => true,
			'width'  => 200,
			'height' => 150
		]);

		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');

		$gd->process($file);

		$this->assertSame([200, 150], array_slice(getimagesize($file), 0, 2));
	}

	public function testResizeWithFocusPoint(): void
	{
		$gd = new GdLib([
			'crop'   => '25% 0%',
			'width'  => 200,
			'height' => 150
		]);

		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');

		$gd->process($file);

		$this->assertSame([200, 150], array_slice(getimagesize($file), 0, 2));
	}

	public function testResizeWithCropRounding(): void
	{
		$gd = new GdLib([
			'crop'   => true,
			'width'  => 200,
			'height' => 289
		]);

		copy(static::FIXTURES . '/../orientation/Landscape_0.jpg', $file = static::TMP . '/landscape.jpg');

		$gd->process($file);

		$this->assertSame([200, 289], array_slice(getimagesize($file), 0, 2));
	}

	public function testResizeCropsAfterDownscaling(): void
	{
		$gd = new GdLib();

		$method = new ReflectionMethod($gd::class, 'resize');

		$simpleImage = new SimpleImageMock();
		$simpleImage->fromNew(4000, 6000);

		$result = $method->invoke($gd, $simpleImage, [
			'crop'         => 'center',
			'sourceWidth'  => 4000,
			'sourceHeight' => 6000,
			'width'        => 300,
			'height'       => 536
		]);

		$this->assertSame(300, $result->getWidth());
		$this->assertSame(536, $result->getHeight());

		$this->assertSame([28, 0, 328, 536], $result->crops[0]);
	}

	public function testResizeCropsAfterDownscalingWithFocusPoint(): void
	{
		$gd = new GdLib();

		$method = new ReflectionMethod($gd::class, 'resize');

		$simpleImage = new SimpleImageMock();
		$simpleImage->fromNew(1800, 1200);

		$result = $method->invoke($gd, $simpleImage, [
			'crop'         => '10% 20%',
			'sourceWidth'  => 1800,
			'sourceHeight' => 1200,
			'width'        => 300,
			'height'       => 536
		]);

		$this->assertSame(300, $result->getWidth());
		$this->assertSame(536, $result->getHeight());

		$this->assertSame([0, 0, 300, 536], $result->crops[0]);
	}

	public function testSharpen(): void
	{
		$gd = new GdLib();

		$method = new ReflectionMethod($gd::class, 'sharpen');

		$simpleImage = new SimpleImageMock();

		$result = $method->invoke($gd, $simpleImage, [
			'sharpen' => 50
		]);

		$this->assertSame(50, $result->sharpen);
	}

	public function testSharpenWithoutValue(): void
	{
		$gd = new GdLib();

		$method = new ReflectionMethod($gd::class, 'sharpen');

		$simpleImage = new SimpleImageMock();

		$result = $method->invoke($gd, $simpleImage, [
			'sharpen' => null
		]);

		$this->assertSame(50, $result->sharpen);
	}
}
