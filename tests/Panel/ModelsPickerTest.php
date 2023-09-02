<?php

namespace Kirby\Panel;

use Kirby\Cms\Collection;
use PHPUnit\Framework\TestCase;

class MockModelsPicker extends ModelsPicker
{
	public function items(): Collection|null
	{
		return new Collection([]);
	}
}

/**
 * @coversDefaultClass \Kirby\Panel\ModelsPicker
 */
class ModelsPickerTest extends TestCase
{
	public function setUp(): void
	{
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$picker   = new MockModelsPicker();
		$defaults = $picker->defaults();

		$this->assertSame([], $defaults['image']);
		$this->assertSame(false, $defaults['info']);
		$this->assertSame('list', $defaults['layout']);
		$this->assertSame(20, $defaults['limit']);
		$this->assertSame(null, $defaults['map']);
		$this->assertSame(1, $defaults['page']);
		$this->assertSame(null, $defaults['query']);
		$this->assertSame(null, $defaults['search']);
		$this->assertSame(null, $defaults['text']);
	}
}
