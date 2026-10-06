<?php

namespace Kirby\Query\Visitors;

use Exception;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\VariableNode;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DefaultVisitor::class)]
class DefaultVisitorTest extends TestCase
{
	public function testArgumentList(): void
	{
		$visitor = new DefaultVisitor();
		$this->assertSame([1, 2, 3], $visitor->arguments([1, 2, 3]));
	}

	public function testArrayList(): void
	{
		$visitor = new DefaultVisitor();
		$this->assertSame([1, 2, 3], $visitor->arrayList([1, 2, 3]));
	}

	public function testClosure(): void
	{
		$visitor = new DefaultVisitor();
		$node    = new ClosureNode(
			arguments: ['a', 'b'],
			body: new CoalesceNode(
				left: new VariableNode('a'),
				right: new VariableNode('b')
			)
		);

		$closure = $visitor->closure($node);

		// Test the behavior instead of comparing closures directly
		$this->assertSame(3, $closure(3, 4));
		$this->assertSame(4, $closure(null, 4));
		$this->assertSame(5, $closure(5, null));
	}

	public function testCoalescence(): void
	{
		$visitor = new DefaultVisitor();
		$this->assertSame(3, $visitor->coalescence(3, fn () => $this->fail('Right side must not be resolved')));
		$this->assertSame(4, $visitor->coalescence(null, fn () => 4));
	}

	public function testComparison(): void
	{
		$visitor = new DefaultVisitor();

		// Equal comparisons
		$this->assertTrue($visitor->comparison(5, '==', 5));
		$this->assertTrue($visitor->comparison('5', '==', 5));
		$this->assertFalse($visitor->comparison(5, '==', 6));

		// Identical comparisons
		$this->assertTrue($visitor->comparison(5, '===', 5));
		$this->assertFalse($visitor->comparison('5', '===', 5));
		$this->assertFalse($visitor->comparison(5, '===', 6));

		// Not equal comparisons
		$this->assertFalse($visitor->comparison(5, '!=', 5));
		$this->assertFalse($visitor->comparison('5', '!=', 5));
		$this->assertTrue($visitor->comparison(5, '!=', 6));

		// Not identical comparisons
		$this->assertFalse($visitor->comparison(5, '!==', 5));
		$this->assertTrue($visitor->comparison('5', '!==', 5));
		$this->assertTrue($visitor->comparison(5, '!==', 6));

		// Less than comparisons
		$this->assertTrue($visitor->comparison(3, '<', 5));
		$this->assertFalse($visitor->comparison(5, '<', 5));
		$this->assertFalse($visitor->comparison(7, '<', 5));

		// Less than or equal comparisons
		$this->assertTrue($visitor->comparison(3, '<=', 5));
		$this->assertTrue($visitor->comparison(5, '<=', 5));
		$this->assertFalse($visitor->comparison(7, '<=', 5));

		// Greater than comparisons
		$this->assertTrue($visitor->comparison(7, '>', 5));
		$this->assertFalse($visitor->comparison(5, '>', 5));
		$this->assertFalse($visitor->comparison(3, '>', 5));

		// Greater than or equal comparisons
		$this->assertTrue($visitor->comparison(7, '>=', 5));
		$this->assertTrue($visitor->comparison(5, '>=', 5));
		$this->assertFalse($visitor->comparison(3, '>=', 5));
	}

	public function testComparisonWithStrings(): void
	{
		$visitor = new DefaultVisitor();

		$this->assertTrue($visitor->comparison('abc', '==', 'abc'));
		$this->assertTrue($visitor->comparison('abc', '===', 'abc'));
		$this->assertFalse($visitor->comparison('abc', '==', 'def'));
		$this->assertTrue($visitor->comparison('abc', '<', 'def'));
		$this->assertTrue($visitor->comparison('abc', '<=', 'abc'));
		$this->assertTrue($visitor->comparison('def', '>', 'abc'));
		$this->assertTrue($visitor->comparison('abc', '>=', 'abc'));
	}

	public function testComparisonWithNull(): void
	{
		$visitor = new DefaultVisitor();

		$this->assertTrue($visitor->comparison(null, '==', null));
		$this->assertTrue($visitor->comparison(null, '===', null));
		$this->assertTrue($visitor->comparison(null, '==', 0)); // null == 0 is true in PHP
		$this->assertFalse($visitor->comparison(null, '!=', 0)); // null != 0 is false in PHP
		$this->assertTrue($visitor->comparison(null, '!==', 0)); // null !== 0 is true in PHP
	}

	public function testComparisonWithBooleans(): void
	{
		$visitor = new DefaultVisitor();

		$this->assertTrue($visitor->comparison(true, '==', true));
		$this->assertTrue($visitor->comparison(true, '===', true));
		$this->assertFalse($visitor->comparison(true, '==', false));
		$this->assertTrue($visitor->comparison(true, '!=', false));
		$this->assertTrue($visitor->comparison(true, '!==', false));
	}

	public function testComparisonWithArrays(): void
	{
		$visitor = new DefaultVisitor();

		$arr1 = [1, 2, 3];
		$arr2 = [1, 2, 3];
		$arr3 = [1, 2, 4];

		$this->assertTrue($visitor->comparison($arr1, '==', $arr2));
		$this->assertTrue($visitor->comparison($arr1, '===', $arr2));
		$this->assertFalse($visitor->comparison($arr1, '==', $arr3));
		$this->assertTrue($visitor->comparison($arr1, '!=', $arr3));
	}

	public function testComparisonInvalidOperator(): void
	{
		$visitor = new DefaultVisitor();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Unknown comparison operator: <=>');
		$visitor->comparison(5, '<=>', 6);
	}

	public function testGlobalFunction(): void
	{
		$visitor = new DefaultVisitor(
			global: ['foo' => fn () => 'bar']
		);
		$this->assertSame('bar', $visitor->function('foo'));
	}

	public function testGlobalFunctionInvalid(): void
	{
		$visitor = new DefaultVisitor();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid global function in query: fox');

		$visitor->function('fox', []);
	}

	public function testLiteral(): void
	{
		$visitor = new DefaultVisitor();
		$this->assertSame(3, $visitor->literal(3));
		$this->assertSame(null, $visitor->literal(null));
	}

	public function testLogical(): void
	{
		$visitor = new DefaultVisitor();
		$unused  = fn () => $this->fail('Right side must not be resolved');

		$this->assertTrue($visitor->logical(true, '&&', fn () => true));
		$this->assertFalse($visitor->logical(true, 'AND', fn () => false));
		$this->assertFalse($visitor->logical(false, '&&', $unused));
		$this->assertFalse($visitor->logical(null, 'AND', $unused));

		$this->assertTrue($visitor->logical(false, '||', fn () => true));
		$this->assertFalse($visitor->logical(false, 'OR', fn () => false));
		$this->assertTrue($visitor->logical(true, '||', $unused));
		$this->assertTrue($visitor->logical('yes', 'OR', $unused));
	}

	public function testLogicalInvalidOperator(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Unknown logical operator: XOR');

		$visitor = new DefaultVisitor();
		$visitor->logical(true, 'XOR', fn () => true);
	}

	public function testMemberAccess(): void
	{
		$visitor = new DefaultVisitor();
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
		$visitor = new DefaultVisitor(interceptor: fn ($obj) => new class () {
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

	public function testNot(): void
	{
		$visitor = new DefaultVisitor();
		$this->assertFalse($visitor->not(true));
		$this->assertTrue($visitor->not(false));

		$this->assertTrue($visitor->not(null));
		$this->assertTrue($visitor->not(0));
		$this->assertTrue($visitor->not(''));
		$this->assertTrue($visitor->not([]));

		$this->assertFalse($visitor->not('foo'));
		$this->assertFalse($visitor->not(1));
		$this->assertFalse($visitor->not(['foo']));
	}

	public function testTernary(): void
	{
		$visitor = new DefaultVisitor();
		$unused  = fn () => $this->fail('Unused branch must not be resolved');

		$this->assertSame(2, $visitor->ternary(true, fn () => 2, $unused));
		$this->assertSame(3, $visitor->ternary(false, $unused, fn () => 3));

		$this->assertSame('truthy', $visitor->ternary('truthy', null, $unused));
		$this->assertSame(3, $visitor->ternary(null, null, fn () => 3));
	}

	public function testVariable(): void
	{
		$visitor = new DefaultVisitor(
			context: [
				'foo' => 'bar',
				'fox' => fn () => 'bax'
			],
			global: [
				'foz' => fn () => 'baz'
			]
		);

		$this->assertSame('bar', $visitor->variable('foo'));
		$this->assertSame('bax', $visitor->variable('fox'));
		$this->assertSame('baz', $visitor->variable('foz'));
		$this->assertNull($visitor->variable('nil'));
	}
}
