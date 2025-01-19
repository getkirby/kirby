<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Transpiler;
use Kirby\Query\Visitors\Interpreter;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\CoalesceNode
 * @covers ::__construct
 */
class CoalesceNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
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
