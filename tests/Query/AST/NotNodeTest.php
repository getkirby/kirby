<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;

class NotNodeTest extends TestCase
{
	public function testResolve()
	{
		$node = new NotNode(
			value: new LiteralNode(true)
		);

		$visitor = new DefaultVisitor();
		$this->assertFalse($node->resolve($visitor));
	}

	public function testResolveNested()
	{
		$node = new NotNode(
			value: new NotNode(
				value: new LiteralNode(false)
			)
		);

		$visitor = new DefaultVisitor();
		$this->assertFalse($node->resolve($visitor));
	}

	public function testResolveTruthy()
	{
		$node = new NotNode(
			value: new LiteralNode(null)
		);

		$visitor = new DefaultVisitor();
		$this->assertTrue($node->resolve($visitor));
	}
}
