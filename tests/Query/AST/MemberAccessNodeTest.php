<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MemberAccessNode::class)]
class MemberAccessNodeTest extends TestCase
{
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

		$visitor = new DefaultVisitor(context: $context);
		$this->assertSame('foo', $node->resolve($visitor));
	}
}
