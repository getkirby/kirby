<?php

namespace Kirby\Template;

/**
 * @coversDefaultClass Kirby\Template\Slots
 */
class SlotsTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::__get
	 * @covers ::__call
	 */
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
