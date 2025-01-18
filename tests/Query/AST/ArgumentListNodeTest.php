<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\CodeGen;
use Kirby\Query\Visitors\Interpreter;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\ArgumentListNode
 * @covers ::__construct
 */
class ArgumentListNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new ArgumentListNode([
			new LiteralNode('a'),
			new LiteralNode(7)
		]);

		// Interpreter
		$visitor = new Interpreter();
		$this->assertSame(['a', 7], $node->resolve($visitor));

		// CodeGen
		$visitor = new CodeGen();
		$this->assertSame('\'a\', 7', $node->resolve($visitor));
	}
}
