<?php

namespace Kirby\Parsley;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Schema::class)]
class SchemaTest extends TestCase
{
	public function testFallback()
	{
		$schema = new Schema();
		return $this->assertNull($schema->fallback('test'));
	}

	public function testMarks()
	{
		$schema = new Schema();
		return $this->assertSame([], $schema->marks());
	}

	public function testNodes()
	{
		$schema = new Schema();
		return $this->assertSame([], $schema->nodes());
	}

	public function testSkip()
	{
		$schema = new Schema();
		return $this->assertSame([], $schema->skip());
	}
}
