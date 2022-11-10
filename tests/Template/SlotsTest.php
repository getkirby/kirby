<?php

namespace Kirby\Template;

/**
 * @coversDefaultClass \Kirby\Template\Slots
 */
class SlotsTest extends TestCase
{
	public function testSlots()
	{
		$component = new Component('test');
		$header    = new Slot($component, 'header');
		$footer    = new Slot($component, 'footer');
		$slots     = new Slots($component, [
			'header' => $header,
			'footer' => $footer
		]);

		$this->assertSame($header, $slots->header);
		$this->assertSame($header, $slots->header());
		$this->assertSame($footer, $slots->footer);
		$this->assertSame($footer, $slots->footer());
	}
}
