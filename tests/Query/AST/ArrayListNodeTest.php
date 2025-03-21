<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArrayListNode::class)]
class ArrayListNodeTest extends TestCase
{
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
