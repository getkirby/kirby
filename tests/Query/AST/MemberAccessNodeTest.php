<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Interpreter;
use Kirby\Query\Visitors\Transpiler;
use Kirby\TestCase;

class UserMock
{
	public function name(): string
	{
		return 'foo';
	}
}

/**
 * @coversDefaultClass \Kirby\Query\AST\MemberAccessNode
 * @covers ::__construct
 */
class MemberAccessNodeTest extends TestCase
{
	/**
	 * @covers ::member
	 */
	public function testMember(): void
	{
		$node = new MemberAccessNode(
			new VariableNode('user'),
			'name'
		);

		$this->assertSame('name', $node->member());

		$node = new MemberAccessNode(
			new VariableNode('user'),
			'my\.name'
		);

		$this->assertSame('my.name', $node->member());

		$node = new MemberAccessNode(
			new VariableNode('user'),
			1
		);

		$this->assertSame(1, $node->member());
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve(): void
	{
		$node = new MemberAccessNode(
			new VariableNode('user'),
			'name'
		);

		$context = ['user' => new UserMock()];

		// Interpreter
		$visitor = new Interpreter(context: $context);
		$this->assertSame('foo', $node->resolve($visitor));

		// Transpiler
		$visitor = new Transpiler(context: $context);
		$this->assertSame('Runtime::access(($intercept($_2375276105)), \'name\', false)', $node->resolve($visitor));
	}
}
