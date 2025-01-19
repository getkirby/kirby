<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\GlobalFunctionNode
 * @covers ::__construct
 */
class GlobalFunctionNodeTest extends TestCase
{
	/**
	 * @covers ::name
	 */
	public function testName(): void
	{
		$node = new GlobalFunctionNode('a', new ArgumentListNode());
		$this->assertSame('a', $node->name());

		$node = new GlobalFunctionNode('a\.b', new ArgumentListNode());
		$this->assertSame('a.b', $node->name());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new GlobalFunctionNode(
			name: 'foo',
			arguments: new ArgumentListNode([
				new LiteralNode(3),
				new LiteralNode(7)
			])
		);

		$functions = ['foo' => fn ($a, $b) => $a + $b];

		// Interpreter
		$visitor = new Interpreter(functions: $functions);
		$this->assertSame(10, $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler(functions: $functions);
		$this->assertSame('$functions[\'foo\'](3, 7)', $node->resolve($visitor));
	}
}
