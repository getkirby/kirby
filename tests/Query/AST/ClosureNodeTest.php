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
		$this->assertEquals(fn ($a, $b) => $a ?? $b, $node->resolve($visitor));
	}
}
