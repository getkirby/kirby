<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
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

		// Interpreter
		$visitor = new Interpreter();
		$this->assertSame('foo', $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler();
		$this->assertSame('(NULL ?? \'foo\')', $node->resolve($visitor));
	}
}
