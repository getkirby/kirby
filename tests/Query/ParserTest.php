<?php

namespace Kirby\Query;

use Kirby\TestCase;
use Kirby\Query\AST\ArgumentListNode;
use Kirby\Query\AST\LiteralNode;
use Kirby\Query\AST\MemberAccessNode;
use Kirby\Query\AST\VariableNode;

class ParserTest extends TestCase
{
	public function testMemberAccess()
	{
		$tokenizer = new Tokenizer('user.name');
		$ast = (new Parser($tokenizer))->parse();
		$this->assertEquals(
			$ast,
			new MemberAccessNode(
				new VariableNode('user'),
				'name'
			)
		);
	}

	public function testSquentialMemberAccess()
	{
		$tokenizer = new Tokenizer('user.name("arg").age');
		$ast = (new Parser($tokenizer))->parse();
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
