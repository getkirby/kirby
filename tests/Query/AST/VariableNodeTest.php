<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VariableNode::class)]
class VariableNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new VariableNode('a');

		$visitor = new DefaultVisitor(context: ['a' => 'foo']);
		$this->assertSame('foo', $node->resolve($visitor));
	}
}
