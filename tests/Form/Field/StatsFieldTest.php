<?php

namespace Kirby\Form\Field;

use Kirby\Panel\Ui\Stats;

class StatsFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('stats');

		$this->assertSame('stats', $field->type());
		$this->assertSame('stats', $field->name());
		$this->assertSame('Stats', $field->label());
		$this->assertSame([], $field->reports());
		$this->assertSame('large', $field->size());
		$this->assertInstanceOf(Stats::class, $field->stats());
	}
}
