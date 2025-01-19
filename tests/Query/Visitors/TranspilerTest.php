<?php

namespace Kirby\Query\Runners;

use Exception;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\VariableNode;
use Kirby\Query\Visitors\Transpiler;

/**
 * @coversDefaultClass \Kirby\Query\Visitors\Transpiler
 * @covers ::__construct
 */
class TranspilerTest extends TestCase
{
	/**
	 * @covers ::argumentList
	 */
	public function testArgumentList(): void
	{
		$visitor = new Transpiler();
		$this->assertSame('1, 2, 3', $visitor->argumentList([1, 2, 3]));
	}

	/**
	 * @covers ::arrayList
	 */
	public function testArrayList(): void
	{
		$visitor = new Transpiler();
		$this->assertSame('[1, 2, 3]', $visitor->arrayList([1, 2, 3]));
	}

	/**
	 * @covers ::closure
	 */
	public function testClosure(): void
	{
		$visitor = new Transpiler();
		$node    = new ClosureNode(
			arguments: ['a', 'b'],
			body: new CoalesceNode(
				left: new VariableNode('a'),
				right: new VariableNode('b')
			)
		);

		$this->assertEquals(
			'fn($_3904355907, $_1908338681) => ($_3904355907 ?? $_1908338681)',
			$visitor->closure($node)
		);
	}

	/**
	 * @covers ::coalescence
	 */
	public function testCoalescence(): void
	{
		$visitor = new Transpiler();
		$this->assertSame('(3 ?? 4)', $visitor->coalescence(3, 4));
		$this->assertSame('( ?? 4)', $visitor->coalescence(null, 4));
	}

	/**
	 * @covers ::function
	 */
	public function testFunction(): void
	{
		$visitor = new Transpiler(
			functions: ['foo' => fn () => 'bar']
		);
		$this->assertSame('$functions[\'foo\']()', $visitor->function('foo', null));
	}

	/**
	 * @covers ::function
	 */
	public function testFunctionInvalid(): void
	{
		$visitor = new Transpiler();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid global function: fox');

		$visitor->function('fox', null);
	}

	/**
	 * @covers ::intercept
	 */
	public function testIntercept(): void
	{
		$visitor = new Transpiler();
		$this->assertSame('($intercept(hello))', $visitor->intercept('hello'));
	}

	/**
	 * @covers ::literal
	 */
	public function testLiteral(): void
	{
		$visitor = new Transpiler();
		$this->assertSame('3', $visitor->literal(3));
		$this->assertSame('NULL', $visitor->literal(null));
	}

	/**
	 * @covers ::memberAccess
	 */
	public function testMemberAccess(): void
	{
		$visitor = new Transpiler();

		$this->assertSame(
			'Runtime::access(($intercept($_user)), \'foo\', false)',
			$visitor->memberAccess('$_user', 'foo')
		);
	}

	/**
	 * @covers ::phpName
	 */
	public function testPhpName(): void
	{
		$this->assertSame('$_907060870', Transpiler::phpName('hello'));
	}

	/**
	 * @covers ::ternary
	 *
	 * TODO: is that right that false/null are missing here
	 */
	public function testTernary(): void
	{
		$visitor = new Transpiler();
		$this->assertSame('(1 ? 2 : 3)', $visitor->ternary(true, 2, 3));
		$this->assertSame('( ? 2 : 3)', $visitor->ternary(false, 2, 3));

		$this->assertSame('(truthy ?: 3)', $visitor->ternary('truthy', null, 3));
		$this->assertSame('( ?: 3)', $visitor->ternary(null, null, 3));
	}

	/**
	 * @covers ::variable
	 */
	public function testVariable(): void
	{
		$visitor = new Transpiler(
			context: ['foo' => 'bar'],
			functions: ['foz' => fn () => 'baz']
		);

		$this->assertSame('$_2356372769', $visitor->variable('foo'));
		$this->assertSame('$_3786310090', $visitor->variable('foz'));
		$this->assertSame('Runtime::get(\'foz\', $context, $functions)', $visitor->mappings['$_3786310090']);
		$this->assertTrue($visitor->uses[Runtime::class]);
	}
}
