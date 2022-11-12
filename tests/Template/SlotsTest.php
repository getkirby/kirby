<?php

namespace Kirby\Template;

class SlotsTest extends TestCase
{
	public function testSlots()
	{
		$component = new Snippet('test');
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
