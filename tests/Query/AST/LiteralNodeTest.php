<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\CodeGen;
use Kirby\Query\Visitors\Interpreter;
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

		// CodeGen
		$visitor = new CodeGen();
		$this->assertSame('\'a\'', $node->resolve($visitor));
	}
}
