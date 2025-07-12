<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArgumentListNode::class)]
class ArgumentListNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new ArgumentListNode([
			new LiteralNode('a'),
			new LiteralNode(7)
		]);

		$visitor = new DefaultVisitor();
		$this->assertSame(['a', 7], $node->resolve($visitor));
	}
}
