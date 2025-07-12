<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CoalesceNode::class)]
class CoalesceNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new CoalesceNode(
			left: new LiteralNode(null),
			right: new LiteralNode('foo')
		);

		$visitor = new DefaultVisitor();
		$this->assertSame('foo', $node->resolve($visitor));
	}
}
