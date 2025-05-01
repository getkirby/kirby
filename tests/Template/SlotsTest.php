<?php

namespace Kirby\Template;

/**
 * @coversDefaultClass \Kirby\Template\Slots
 */
class SlotsTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::__get
	 * @covers ::__call
	 * @covers ::count
	 */
	public function testSlots()
	{
		$header = new Slot('header');
		$footer = new Slot('footer');
		$slots  = new Slots([
			'header' => $header,
			'footer' => $footer
		]);

		$this->assertSame($header, $slots->header);
		$this->assertSame($header, $slots->header());
		$this->assertSame($footer, $slots->footer);
		$this->assertSame($footer, $slots->footer());
		$this->assertCount(2, $slots);
	}
}
