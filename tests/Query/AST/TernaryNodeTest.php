<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Transpiler;
use Kirby\Query\Visitors\Interpreter;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\TernaryNode
 * @covers ::__construct
 */
class TernaryNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
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
