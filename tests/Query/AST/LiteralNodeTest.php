<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LiteralNode::class)]
class LiteralNodeTest extends TestCase
{
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
