<?php

namespace Kirby\Query\AST;

use DivisionByZeroError;
use Exception;
use Kirby\Query\Visitors\DefaultVisitor;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ArithmeticNode::class)]
class ArithmeticNodeTest extends TestCase
{
	public static function arithmeticProvider(): array
	{
		return [
			['+', 5, 3, 8],
			['-', 5, 3, 2],
			['*', 5, 3, 15],
			['/', 6, 2, 3],
			['%', 7, 3, 1],
		];
	}

	#[DataProvider('arithmeticProvider')]
	public function testResolve(
		string $operator,
		int|float $left,
		int|float $right,
		int|float $expected
	): void {
		$node = new ArithmeticNode(
			left: new LiteralNode($left),
			operator: $operator,
			right: new LiteralNode($right)
		);

		$visitor = new DefaultVisitor();
		$this->assertSame($expected, $node->resolve($visitor));
	}

	public function testResolveWithVariables(): void
	{
		$node = new ArithmeticNode(
			left: new VariableNode('a'),
			operator: '+',
			right: new VariableNode('b')
		);

		$context = ['a' => 5, 'b' => 3];

		$visitor = new DefaultVisitor(context: $context);
		$this->assertSame(8, $node->resolve($visitor));
	}

	public function testResolveWithComplexExpressions(): void
	{
		$node = new ArithmeticNode(
			left: new ArithmeticNode(
				left: new LiteralNode(2),
				operator: '*',
				right: new LiteralNode(3)
			),
			operator: '+',
			right: new LiteralNode(4)
		);

		$visitor = new DefaultVisitor();
		$this->assertSame(10, $node->resolve($visitor));
	}

	public function testResolveWithFloats(): void
	{
		$node = new ArithmeticNode(
			left: new LiteralNode(5.5),
			operator: '*',
			right: new LiteralNode(2.0)
		);

		$visitor = new DefaultVisitor();
		$this->assertSame(11.0, $node->resolve($visitor));
	}

	public function testResolveWithNegativeNumbers(): void
	{
		$node = new ArithmeticNode(
			left: new LiteralNode(-5),
			operator: '+',
			right: new LiteralNode(3)
		);

		$visitor = new DefaultVisitor();
		$this->assertSame(-2, $node->resolve($visitor));
	}

	public function testResolveWithDivisionByZero(): void
	{
		$node = new ArithmeticNode(
			left: new LiteralNode(5),
			operator: '/',
			right: new LiteralNode(0)
		);

		// Visitor should throw exception for division by zero
		$visitor = new DefaultVisitor();
		$this->expectException(DivisionByZeroError::class);
		$node->resolve($visitor);
	}

	public function testResolveWithModuloByZero(): void
	{
		$node = new ArithmeticNode(
			left: new LiteralNode(5),
			operator: '%',
			right: new LiteralNode(0)
		);

		// Visitor should throw exception for modulo by zero
		$visitor = new DefaultVisitor();
		$this->expectException(DivisionByZeroError::class);
		$node->resolve($visitor);
	}

	public function testResolveWithInvalidOperator(): void
	{
		$node = new ArithmeticNode(
			left: new LiteralNode(5),
			operator: '^',
			right: new LiteralNode(3)
		);

		// Visitor should throw exception for invalid operator
		$visitor = new DefaultVisitor();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Unknown arithmetic operator: ^');
		$node->resolve($visitor);
	}
}
