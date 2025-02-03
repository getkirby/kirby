<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\ClosureNode
 * @covers ::__construct
 */
class ClosureNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new ClosureNode(
			arguments: ['a', 'b'],
			body: new CoalesceNode(
				left: new VariableNode('a'),
				right: new VariableNode('b')
			)
		);

		// Interpreter
		$visitor = new Interpreter();
		$this->assertEquals(fn ($a, $b) => $a ?? $b, $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler();
		$this->assertSame(
			'fn($_3904355907, $_1908338681) => ($_3904355907 ?? $_1908338681)',
			$node->resolve($visitor)
		);
	}
}
