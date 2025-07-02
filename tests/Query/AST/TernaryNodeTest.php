<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TernaryNode::class)]
class TernaryNodeTest extends TestCase
{
	public function testResolve(): void
	{
		$node = new TernaryNode(
			condition: new LiteralNode(false),
			false: new LiteralNode(5.0)
		);

		// Interpreter
		$visitor = new Interpreter();
		$this->assertSame(5.0, $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler();
		$this->assertSame('(false ?: 5.0)', $node->resolve($visitor));
	}
}
