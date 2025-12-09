<?php

namespace Kirby\Image;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Gravity::class)]
class GravityTest extends TestCase
{
	public function testToPercentageString(): void
	{
		$this->assertSame('50% 0%', Gravity::TOP->toPercentageString());
		$this->assertSame('0% 0%', Gravity::TOP_LEFT->toPercentageString());
		$this->assertSame('100% 0%', Gravity::TOP_RIGHT->toPercentageString());

		$this->assertSame('0% 50%', Gravity::LEFT->toPercentageString());
		$this->assertSame('50% 50%', Gravity::CENTER->toPercentageString());
		$this->assertSame('100% 50%', Gravity::RIGHT->toPercentageString());

		$this->assertSame('50% 100%', Gravity::BOTTOM->toPercentageString());
		$this->assertSame('0% 100%', Gravity::BOTTOM_LEFT->toPercentageString());
		$this->assertSame('100% 100%', Gravity::BOTTOM_RIGHT->toPercentageString());
	}
}
