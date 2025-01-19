<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\LiteralNode
 * @covers ::__construct
 */
class LiteralNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new LiteralNode('a');

		// Interpreter
		$visitor = new Interpreter();
		$this->assertSame('a', $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler();
		$this->assertSame('\'a\'', $node->resolve($visitor));
	}
}
