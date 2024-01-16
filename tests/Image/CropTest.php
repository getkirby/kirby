<?php

namespace Kirby\Image;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Crop
 */
class CropTest extends TestCase
{
	/**
	 * @covers ::focus
	 */
	public function testFocus()
	{
		$this->assertSame([0.7, 0.3], Crop::focus('70%, 30%'));
		$this->assertSame([0.7, 0.3], Crop::focus('70%,30%'));
		$this->assertSame([0.7, 0.3], Crop::focus('70% 30%'));
		$this->assertSame([0.7, 0.3], Crop::focus('70,30'));
		$this->assertSame([0.7, 0.3], Crop::focus('0.7, 0.3'));
		$this->assertSame([0.704, 0.304], Crop::focus('70.4,30.4'));
		$this->assertSame([0.7, 0.3], Crop::focus('{"x":0.7,"y":0.3}'));
	}

	/**
	 * @covers ::__construct
	 */
	public function testCrop()
	{
		$crop = new Crop(
			sourceWidth: 1200,
			sourceHeight: 700,
			targetWidth: 400,
			targetHeight: 250,
			focus: 'top left'
		);
		$this->assertSame(0, $crop->x1);
		$this->assertSame(0, $crop->y1);
		$this->assertSame(1120, $crop->x2);
		$this->assertSame(700, $crop->y2);
		$this->assertSame(1120, $crop->scaledWidth);
		$this->assertSame(700, $crop->scaledHeight);

		$crop = new Crop(
			sourceWidth: 1200,
			sourceHeight: 700,
			targetWidth: 400,
			targetHeight: 250,
			focus: 'top right'
		);
		$this->assertSame(80, $crop->x1);
		$this->assertSame(0, $crop->y1);
		$this->assertSame(1200, $crop->x2);
		$this->assertSame(700, $crop->y2);
		$this->assertSame(1120, $crop->scaledWidth);
		$this->assertSame(700, $crop->scaledHeight);
	}
}
