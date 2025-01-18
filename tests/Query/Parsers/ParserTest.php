<?php

namespace Kirby\Query\Parsers;

use Kirby\TestCase;
use Kirby\Query\AST\ArgumentListNode;
use Kirby\Query\AST\LiteralNode;
use Kirby\Query\AST\MemberAccessNode;
use Kirby\Query\AST\VariableNode;

/**
 * @coversDefaultClass \Kirby\Query\Parsers\Parser
 */
class ParserTest extends TestCase
{
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
}
