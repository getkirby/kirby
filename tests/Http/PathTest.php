<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Http\Path
 */
class PathTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstructWithArray()
	{
		$path = new Path(['docs', 'reference']);

		$this->assertCount(2, $path);
		$this->assertSame('docs', $path->first());
		$this->assertSame('reference', $path->last());
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructWithString()
	{
		$path = new Path('/docs/reference');

		$this->assertCount(2, $path);
		$this->assertSame('docs', $path->first());
		$this->assertSame('reference', $path->last());
	}

	/**
	 * @covers ::__toString
	 * @covers ::toString
	 */
	public function testToString()
	{
		$path = new Path('/docs/reference');
		$this->assertSame('docs/reference', $path->toString());
		$this->assertSame('docs/reference', $path->__toString());
		$this->assertSame('docs/reference', (string)$path);
	}

	/**
	 * @covers ::__toString
	 * @covers ::toString
	 */
	public function testToStringWithLeadingSlash()
	{
		$path = new Path('/docs/reference');
		$this->assertSame('/docs/reference', $path->toString(true));
	}

	/**
	 * @covers ::__toString
	 * @covers ::toString
	 */
	public function testToStringWithLeadingAndTrailingSlash()
	{
		$path = new Path('/docs/reference');
		$this->assertSame('/docs/reference/', $path->toString(true, true));
	}
}
