<?php

namespace Kirby\Template;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Slots::class)]
class SlotsTest extends TestCase
{
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
