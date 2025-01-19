<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\MemberAccessNode
 * @covers ::__construct
 */
class MemberAccessNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new MemberAccessNode(
			new VariableNode('user'),
			new LiteralNode('name')
		);

		$context = ['user' => new class () {
			public function name(): string
			{
				return 'foo';
			}
		}];

		// Interpreter
		$visitor = new Interpreter(context: $context);
		$this->assertSame('foo', $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler(context: $context);
		$this->assertSame('Runtime::access(($intercept($_2375276105)), \'name\', false)', $node->resolve($visitor));
	}
}
