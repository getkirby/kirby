<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\ArrayListNode
 * @covers ::__construct
 */
class ArrayListNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new ArrayListNode([
			new LiteralNode('a'),
			new LiteralNode(7)
		]);

		// Interpreter
		$visitor = new Interpreter();
		$this->assertSame(['a', 7], $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler();
		$this->assertSame('[\'a\', 7]', $node->resolve($visitor));
	}
}
