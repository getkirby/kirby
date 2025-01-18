<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\CodeGen;
use Kirby\Query\Visitors\Interpreter;
use Kirby\TestCase;

class UserMock {
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

		$context = ['user' => new UserMock];

		// Interpreter
		$visitor = new Interpreter(context: $context);
		$this->assertSame('foo', $node->resolve($visitor));

		// CodeGen
		$visitor = new CodeGen(context: $context);
		$this->assertSame('Runtime::access(($intercept($_2375276105)), \'name\', false)', $node->resolve($visitor));
	}
}
