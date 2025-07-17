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
		$this->assertFalse($field->hasValue());
		$this->assertSame([], $field->reports());
		$this->assertSame('large', $field->size());
		$this->assertInstanceOf(Stats::class, $field->stats());
		$this->assertSame([
			'disabled' => false,
			'hidden'   => false,
			'name'     => 'stats',
			'saveable' => false,
			'type'     => 'stats',
			'when'     => null,
			'width'    => '1/1',
			'help'     => null,
			'label'    => 'Stats',
			'reports'  => [],
			'size'     => 'large'
		], $field->props());
	}
}
