<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClosureNode::class)]
class ClosureNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new ClosureNode(
			arguments: ['a', 'b'],
			body: new CoalesceNode(
				left: new VariableNode('a'),
				right: new VariableNode('b')
			)
		);

		$visitor = new DefaultVisitor();
		$closure = $node->resolve($visitor);

		// Test the behavior instead of comparing closures directly
		$this->assertSame(3, $closure(3, 4));
		$this->assertSame(4, $closure(null, 4));
		$this->assertSame(5, $closure(5, null));
	}
}
