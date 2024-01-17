<?php

namespace Kirby\Image\Darkroom;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Darkroom\GdLib
 */
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

	public function testProcess()
	{
		$gd = new GdLib();

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
			'width' => 500,
			'sourceWidth' => 500,
			'sourceHeight' => 500,
		], $gd->process($file));
	}

	/**
	 * @covers ::mime
	 */
	public function testProcessWithFormat()
	{
		$gd = new GdLib(['format' => 'webp']);
		copy(static::FIXTURES . '/cat.jpg', $file = static::TMP . '/cat.jpg');
		$this->assertSame('webp', $gd->process($file)['format']);
	}
}
