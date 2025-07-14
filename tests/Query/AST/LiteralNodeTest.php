<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LiteralNode::class)]
class LiteralNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new LiteralNode('a');

		$visitor = new DefaultVisitor();
		$this->assertSame('a', $node->resolve($visitor));
	}
}
