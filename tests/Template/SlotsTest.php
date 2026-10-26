<?php

namespace Kirby\Template;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Slots::class)]
class SlotsTest extends TestCase
{
	public function testSlots(): void
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

	public function testIsset(): void
	{
		$slots = new Slots([
			'header' => new Slot('header')
		]);

		$this->assertTrue(isset($slots->header));
		$this->assertFalse(empty($slots->header));
		$this->assertFalse(isset($slots->footer));
		$this->assertTrue(empty($slots->footer));
	}
}
