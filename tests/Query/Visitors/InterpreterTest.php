<?php

namespace Kirby\Query\Runners;

use Exception;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\VariableNode;
use Kirby\Query\Visitors\Interpreter;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Interpreter::class)]
class InterpreterTest extends TestCase
{
	public function testArgumentList(): void
	{
		$visitor = new Interpreter();
		$this->assertSame([1, 2, 3], $visitor->argumentList([1, 2, 3]));
	}

	public function testArrayList(): void
	{
		$visitor = new Interpreter();
		$this->assertSame([1, 2, 3], $visitor->arrayList([1, 2, 3]));
	}

	public function testClosure(): void
	{
		$visitor = new Interpreter();
		$node    = new ClosureNode(
			arguments: ['a', 'b'],
			body: new CoalesceNode(
				left: new VariableNode('a'),
				right: new VariableNode('b')
			)
		);

		$this->assertEquals(
			fn ($a, $b) => $a ?? $b,
			$closure = $visitor->closure($node)
		);
		$this->assertSame(3, $closure(3, 4));
	}

	public function testCoalescence(): void
	{
		$visitor = new Interpreter();
		$this->assertSame(3, $visitor->coalescence(3, 4));
		$this->assertSame(4, $visitor->coalescence(null, 4));
	}

	public function testFunction(): void
	{
		$visitor = new Interpreter(
			functions: ['foo' => fn () => 'bar']
		);
		$this->assertSame('bar', $visitor->function('foo'));
	}

	public function testFunctionInvalid(): void
	{
		$visitor = new Interpreter();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid global function: fox');

		$visitor->function('fox', []);
	}

	public function testLiteral(): void
	{
		$visitor = new Interpreter();
		$this->assertSame(3, $visitor->literal(3));
		$this->assertSame(null, $visitor->literal(null));
	}

	public function testMemberAccess(): void
	{
		$visitor = new Interpreter();
		$obj     = new class () {
			public function foo(): string
			{
				return 'bar';
			}
		};

		$this->assertSame('bar', $visitor->memberAccess($obj, 'foo'));
	}

	public function testMemberAccessWithInterceptor(): void
	{
		$visitor = new Interpreter(interceptor: fn ($obj) => new class () {
			public function foo(): string
			{
				return 'baz';
			}
		});

		$obj = new class () {
			public function foo(): string
			{
				return 'bar';
			}
		};

		$this->assertSame('baz', $visitor->memberAccess($obj, 'foo'));
	}

	public function testTernary(): void
	{
		$visitor = new Interpreter();
		$this->assertSame(2, $visitor->ternary(true, 2, 3));
		$this->assertSame(3, $visitor->ternary(false, 2, 3));

		$this->assertSame('truthy', $visitor->ternary('truthy', null, 3));
		$this->assertSame(3, $visitor->ternary(null, null, 3));
	}

	public function testVariable(): void
	{
		$visitor = new Interpreter(
			context: [
				'foo' => 'bar',
				'fox' => fn () => 'bax'
			],
			functions: [
				'foz' => fn () => 'baz'
			]
		);

		$this->assertSame('bar', $visitor->variable('foo'));
		$this->assertSame('bax', $visitor->variable('fox'));
		$this->assertSame('baz', $visitor->variable('foz'));
		$this->assertNull($visitor->variable('nil'));
	}
}
