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
		$this->assertFalse($field->hasValue());
		$this->assertInstanceOf(Stats::class, $field->stats());

		$props = $field->props();

		$this->assertSame('Stats', $props['label']);
		$this->assertSame('stats', $props['name']);
		$this->assertSame('stats', $props['type']);
		$this->assertSame([], $props['reports']);
		$this->assertSame('large', $props['size']);
	}
}
