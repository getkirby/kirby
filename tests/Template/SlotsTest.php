<?php

namespace Kirby\Template;

/**
 * @coversDefaultClass \Kirby\Template\Slots
 */
class SlotsTest extends TestCase
{
	public function testSlots()
	{
		$container = new Container('test');
		$header    = new Slot($container, 'header');
		$footer    = new Slot($container, 'footer');
		$slots     = new Slots($container, [
			'header' => $header,
			'footer' => $footer
		]);

		$this->assertSame($header, $slots->header);
		$this->assertSame($header, $slots->header());
		$this->assertSame($footer, $slots->footer);
		$this->assertSame($footer, $slots->footer());
	}
}
