<?php

namespace Kirby\Image;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Focus
 */
class FocusTest extends TestCase
{
	/**
	 * @covers ::coords
	 */
	public function testCoords()
	{
		$options = [
			'sourceWidth'  => 1200,
			'sourceHeight' => 700,
			'width'        => 400,
			'height'       => 350
		];

		$options['crop'] = '0%, 0%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(800, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(700, $focus['y2']);

		$options['crop'] = '100%, 0%';
		$focus = Focus::coords(...$options);
		$this->assertSame(400, $focus['x1']);
		$this->assertSame(1200, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(700, $focus['y2']);

		$options['crop'] = '0%, 100%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(800, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(700, $focus['y2']);

		$options['crop'] = '100%, 100%';
		$focus = Focus::coords(...$options);
		$this->assertSame(400, $focus['x1']);
		$this->assertSame(1200, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(700, $focus['y2']);

		$options['crop'] = '50%, 50%';
		$focus = Focus::coords(...$options);
		$this->assertSame(200, $focus['x1']);
		$this->assertSame(1000, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(700, $focus['y2']);

		$options = [
			'sourceWidth'  => 700,
			'sourceHeight' => 1200,
			'width'        => 400,
			'height'       => 350
		];

		$options['crop'] = '0%, 0%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(700, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(612, $focus['y2']);

		$options['crop'] = '100%, 0%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(700, $focus['x2']);
		$this->assertSame(0, $focus['y1']);
		$this->assertSame(612, $focus['y2']);

		$options['crop'] = '0%, 100%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(700, $focus['x2']);
		$this->assertSame(587, $focus['y1']);
		$this->assertSame(1200, $focus['y2']);

		$options['crop'] = '100%, 100%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(700, $focus['x2']);
		$this->assertSame(587, $focus['y1']);
		$this->assertSame(1200, $focus['y2']);

		$options['crop'] = '50%, 50%';
		$focus = Focus::coords(...$options);
		$this->assertSame(0, $focus['x1']);
		$this->assertSame(700, $focus['x2']);
		$this->assertSame(293, $focus['y1']);
		$this->assertSame(906, $focus['y2']);
	}

	/**
	 * @covers ::coords
	 */
	public function testCoordsSameRatio()
	{
		$options = [
			'sourceWidth'  => 1200,
			'sourceHeight' => 700,
			'width'        => 600,
			'height'       => 350,
			'crop'         => '30% 70%'
		];

		$this->assertNull(Focus::coords(...$options));
	}

	/**
	 * @covers ::parse
	 */
	public function testParse()
	{
		$this->assertSame([0.7, 0.3], Focus::parse('70%, 30%'));
		$this->assertSame([0.7, 0.3], Focus::parse('70%,30%'));
		$this->assertSame([0.7, 0.3], Focus::parse('70% 30%'));
		$this->assertSame([0.7, 0.3], Focus::parse('70,30'));
		$this->assertSame([0.7, 0.3], Focus::parse('0.7, 0.3'));
		$this->assertSame([0.704, 0.304], Focus::parse('70.4,30.4'));
		$this->assertSame([0.7, 0.3], Focus::parse('{"x":0.7,"y":0.3}'));
	}

	/**
	 * @covers ::ratio
	 */
	public function testRatio()
	{
		$this->assertSame(0.5, Focus::ratio(200, 400));
		$this->assertSame(2.0, Focus::ratio(400, 200));
		$this->assertSame(0.25, Focus::ratio(100, 400));
		$this->assertSame(0.0, Focus::ratio(100, 0));
	}
}
