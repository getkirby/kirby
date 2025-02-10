<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Path::class)]
class PathTest extends TestCase
{
	public function testConstructWithArray()
	{
		$path = new Path(['docs', 'reference']);

		$this->assertCount(2, $path);
		$this->assertSame('docs', $path->first());
		$this->assertSame('reference', $path->last());
	}

	public function testConstructWithString()
	{
		$path = new Path('/docs/reference');

		$this->assertCount(2, $path);
		$this->assertSame('docs', $path->first());
		$this->assertSame('reference', $path->last());
	}

	public function testToString()
	{
		$path = new Path('/docs/reference');
		$this->assertSame('docs/reference', $path->toString());
		$this->assertSame('docs/reference', $path->__toString());
		$this->assertSame('docs/reference', (string)$path);
	}

	public function testToStringWithLeadingSlash()
	{
		$path = new Path('/docs/reference');
		$this->assertSame('/docs/reference', $path->toString(true));
	}

	public function testToStringWithLeadingAndTrailingSlash()
	{
		$path = new Path('/docs/reference');
		$this->assertSame('/docs/reference/', $path->toString(true, true));
	}
}
