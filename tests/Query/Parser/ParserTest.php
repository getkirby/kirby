<?php

namespace Kirby\Query\Parser;

use Exception;
use Kirby\Query\AST\ArgumentListNode;
use Kirby\Query\AST\ArrayListNode;
use Kirby\Query\AST\ClosureNode;
use Kirby\Query\AST\CoalesceNode;
use Kirby\Query\AST\GlobalFunctionNode;
use Kirby\Query\AST\LiteralNode;
use Kirby\Query\AST\MemberAccessNode;
use Kirby\Query\AST\TernaryNode;
use Kirby\Query\AST\VariableNode;
use Kirby\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \Kirby\Query\Parser\Parser
 */
class ParserTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstructor(): void
	{
		$parser = new Parser('user.name');
		$this->assertSame('user', $parser->current()->lexeme);
	}

	/**
	 * @covers ::advance
	 * @covers ::current
	 */
	public function testAdvance(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$advance = $class->getMethod('advance');

		$this->assertSame(TokenType::T_IDENTIFIER, $parser->current()->type);
		$advance->invoke($parser);
		$this->assertSame(TokenType::T_DOT, $parser->current()->type);
		$advance->invoke($parser);
		$this->assertSame(TokenType::T_IDENTIFIER, $parser->current()->type);
		$advance->invoke($parser);
		$this->assertSame(TokenType::T_EOF, $parser->current()->type);
		$advance->invoke($parser);
		$this->assertSame(TokenType::T_EOF, $parser->current()->type);
	}

	/**
	 * @covers ::argumentList
	 */
	public function testArgumentList(): void
	{
		$parser = new Parser('site.method(a, b, c)');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new MemberAccessNode(
				object: new VariableNode('site'),
				member: 'method',
				arguments: new ArgumentListNode([
					new VariableNode('a'),
					new VariableNode('b'),
					new VariableNode('c'),
				])
			)
		);
	}

	/**
	 * @covers ::array
	 * @covers ::atomic
	 */
	public function testArray(): void
	{
		$parser = new Parser('[1, 2, 3]');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new ArrayListNode([
				new LiteralNode(1),
				new LiteralNode(2),
				new LiteralNode(3)
			])
		);

		// no array to parse/consume
		$parser = new Parser('foo');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new VariableNode('foo')
		);
	}

	/**
	 * @covers ::coalesce
	 * @covers ::expression
	 */
	public function testCoalesce(): void
	{
		$parser = new Parser('a ?? b');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new CoalesceNode(
				new VariableNode('a'),
				new VariableNode('b')
			)
		);
	}

	/**
	 * @covers ::consume
	 */
	public function testConsume(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$consume = $class->getMethod('consume');
		$token   = $consume->invokeArgs($parser, [TokenType::T_IDENTIFIER]);
		$this->assertSame('user', $token->lexeme);
	}

	/**
	 * @covers ::consume
	 */
	public function testConsumeInvalidType(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$consume = $class->getMethod('consume');
		$this->assertFalse($consume->invokeArgs($parser, [TokenType::T_TRUE]));
	}

	/**
	 * @covers ::consumeAny
	 */
	public function testConsumeAny(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$consume = $class->getMethod('consumeAny');
		$token   = $consume->invokeArgs($parser, [[TokenType::T_IDENTIFIER, TokenType::T_DOT]]);
		$this->assertSame('user', $token->lexeme);
	}

	/**
	 * @covers ::consumeAny
	 */
	public function testConsumeInvalidTypeInvalidType(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$consume = $class->getMethod('consumeAny');
		$this->assertFalse($consume->invokeArgs($parser, [[TokenType::T_TRUE, TokenType::T_FALSE]]));
	}

	/**
	 * @covers ::consume
	 */
	public function testConsumeInvalidTypeCustomError(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$consume = $class->getMethod('consume');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('foo');
		$consume->invokeArgs($parser, [TokenType::T_TRUE, 'foo']);
	}

	/**
	 * @covers ::consumeList
	 */
	public function testConsumeList(): void
	{
		$parser  = new Parser('1, 2)');
		$class   = new ReflectionClass($parser);
		$consume = $class->getMethod('consumeList');
		$list    = $consume->invokeArgs($parser, [TokenType::T_CLOSE_PAREN]);
		$this->assertEquals([new LiteralNode(1), new LiteralNode(2)], $list);
	}

	/**
	 * @covers ::atomic
	 * @covers ::grouping
	 */
	public function testGrouping(): void
	{
		// groupings
		$parser = new Parser('(a ?: b)');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new TernaryNode(
				new VariableNode('a'),
				new VariableNode('b'),
				null,
				true
			)
		);

		// closure
		$parser = new Parser('(a, b) => a');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new ClosureNode(
				['a', 'b'],
				new VariableNode('a')
			)
		);
	}

	/**
	 * @covers ::grouping
	 */
	public function testGroupingClosureInvalidArgument(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Expecting only variables in closure argument list');

		$parser = new Parser('(a, 2) => a');
		$parser->parse();
	}

	/**
	 * @covers ::grouping
	 */
	public function testGroupingClosureInvalidNotation(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Expecting "=>" after closure argument list');

		$parser = new Parser('(a, b) a');
		$parser->parse();
	}


	/**
	 * @covers ::atomic
	 * @covers ::identifier
	 */
	public function testIdentifier(): void
	{
		// global functions
		$parser = new Parser('user("editor")');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new GlobalFunctionNode(
				'user',
				new ArgumentListNode([
					new LiteralNode('editor')
				])
			)
		);

		// variable
		$parser = new Parser('user');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new VariableNode('user')
		);

		// no identifier to parse/consume
		$parser = new Parser('(1 ?? 2)');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new CoalesceNode(
				left: new LiteralNode(1),
				right: new LiteralNode(2)
			)
		);
	}

	/**
	 * @covers ::is
	 */
	public function testIs(): void
	{
		$parser = new Parser('user.name');
		$class  = new ReflectionClass($parser);
		$is     = $class->getMethod('is');

		$this->assertTrue($is->invokeArgs($parser, [TokenType::T_IDENTIFIER]));

		$advance = $class->getMethod('advance');
		$advance->invoke($parser);
		$advance->invoke($parser);
		$advance->invoke($parser);

		$this->assertFalse($is->invokeArgs($parser, [TokenType::T_IDENTIFIER]));
	}

	/**
	 * @covers ::isAtEnd
	 */
	public function testIsAtEnd(): void
	{
		$parser  = new Parser('user.name');
		$class   = new ReflectionClass($parser);
		$isAtEnd = $class->getMethod('isAtEnd');

		$this->assertFalse($isAtEnd->invoke($parser));

		$advance = $class->getMethod('advance');
		$advance->invoke($parser);
		$this->assertFalse($isAtEnd->invoke($parser));

		$advance->invoke($parser);
		$this->assertFalse($isAtEnd->invoke($parser));

		$advance->invoke($parser);
		$this->assertTrue($isAtEnd->invoke($parser));
	}

	/**
	 * @covers ::memberAccess
	 */
	public function testMemberAccess(): void
	{
		$parser = new Parser('user.name');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new MemberAccessNode(
				new VariableNode('user'),
				'name'
			)
		);
	}

	/**
	 * @covers ::memberAccess
	 */
	public function testMemberAccessIntegerIndex(): void
	{
		$parser = new Parser('user.1');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new MemberAccessNode(
				new VariableNode('user'),
				1
			)
		);
	}

	/**
	 * @covers ::memberAccess
	 */
	public function testMemberAccessSequential(): void
	{
		$parser = new Parser('user.name("arg").age');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new MemberAccessNode(
				new MemberAccessNode(
					new VariableNode('user'),
					'name',
					new ArgumentListNode([new LiteralNode('arg')])
				),
				'age'
			)
		);
	}

	/**
	 * @covers ::memberAccess
	 */
	public function testMemberAccessInvalid(): void
	{
		$parser = new Parser('user.true');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Expect property name after "."');
		$parser->parse();
	}

	/**
	 * @covers ::parse
	 */
	public function testParse(): void
	{
		$parser = new Parser('site.method(5) ?: ("fox" ?? false)');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new TernaryNode(
				condition: new MemberAccessNode(
					object: new VariableNode('site'),
					member: 'method',
					arguments: new ArgumentListNode([
						new LiteralNode(5)
					])
				),
				false: new CoalesceNode(
					left: new LiteralNode('fox'),
					right: new LiteralNode(false),
				)
			)
		);
	}

	/**
	 * @covers ::atomic
	 * @covers ::scalar
	 */
	public function testScalar(): void
	{
		$parser = new Parser('true');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new LiteralNode(true)
		);

		$parser = new Parser('-5.74');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new LiteralNode(-5.74)
		);

		// no scalar to parse
		$parser = new Parser('user');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new VariableNode('user')
		);
	}

	/**
	 * @covers ::atomic
	 * @covers ::coalesce
	 * @covers ::ternary
	 */
	public function testTernary(): void
	{
		$parser = new Parser('true ? 2 : 5.0');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new TernaryNode(
				condition: new LiteralNode(true),
				true: new LiteralNode(2),
				false: new LiteralNode(5.0),
			)
		);

		$parser = new Parser('"foo" ?: \'bar\'');
		$ast    = $parser->parse();

		$this->assertEquals(
			$ast,
			new TernaryNode(
				condition: new LiteralNode('foo'),
				false: new LiteralNode('bar')
			)
		);
	}
}
