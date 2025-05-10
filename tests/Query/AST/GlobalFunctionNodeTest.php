<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GlobalFunctionNode::class)]
class GlobalFunctionNodeTest extends TestCase
{
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
		$this->assertSame(
			'$functions[\'foo\'](3, 7)',
			$node->resolve($visitor)
		);
	}
}
