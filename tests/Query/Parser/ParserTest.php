<?php

namespace Kirby\Query\Parser;

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
	 * @covers ::atomic
	 */
	public function testAtomic()
	{
		// primitives/scalars
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

		// arrays
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
	 * @covers ::coalesce
	 * @covers ::expression
	 */
	public function testCoalesce()
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
	 * @covers ::memberAccess
	 */
	public function testMemberAccess()
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
	public function testSequentialMemberAccess()
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
	 * @covers ::atomic
	 * @covers ::coalesce
	 * @covers ::ternary
	 */
	public function testTernary()
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
