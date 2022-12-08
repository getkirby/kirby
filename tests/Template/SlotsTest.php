<?php

namespace Kirby\Template;

class SlotsTest extends TestCase
{
	public function testSlots()
	{
		$snippet = new Snippet('test.php');
		$header  = new Slot($snippet, 'header');
		$footer  = new Slot($snippet, 'footer');
		$slots   = new Slots($snippet, [
			'header' => $header,
			'footer' => $footer
		]);

		$this->assertSame($header, $slots->header);
		$this->assertSame($header, $slots->header());
		$this->assertSame($footer, $slots->footer);
		$this->assertSame($footer, $slots->footer());
	}
}
