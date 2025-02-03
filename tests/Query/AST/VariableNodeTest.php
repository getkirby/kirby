<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\AST\VariableNode
 * @covers ::__construct
 */
class VariableNodeTest extends TestCase
{
	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new VariableNode('a');

		// Interpreter
		$visitor = new Interpreter(context: ['a' => 'foo']);
		$this->assertSame('foo', $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler();
		$this->assertSame('$_3904355907', $node->resolve($visitor));
		$this->assertArrayHasKey('$_3904355907', $visitor->mappings);
	}
}
