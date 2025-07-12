<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArrayListNode::class)]
class ArrayListNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new ArrayListNode([
			new LiteralNode('a'),
			new LiteralNode(7)
		]);

		$visitor = new DefaultVisitor();
		$this->assertSame(['a', 7], $node->resolve($visitor));
	}
}
