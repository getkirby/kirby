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

	public function sharpen(int $amount = 50): static
	{
		$this->sharpen = $amount;
		return $this;
	}
}


#[CoversClass(GdLib::class)]
class GdLibTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/../fixtures/image';
	public const TMP      = KIRBY_TMP_DIR . '/Image.Darkroom.GdLib';

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

	public function testSharpen(): void
	{
		$gd = new GdLib();

		$method = new ReflectionMethod($gd::class, 'sharpen');
		$method->setAccessible(true);

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
		$method->setAccessible(true);

		$simpleImage = new SimpleImageMock();

		$result = $method->invoke($gd, $simpleImage, [
			'sharpen' => null
		]);

		$this->assertSame(50, $result->sharpen);
	}
}
