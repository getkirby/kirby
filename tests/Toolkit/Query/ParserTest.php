<?php

namespace Kirby\Toolkit\Query;
use Kirby\TestCase;
use Kirby\Toolkit\Query\AST\ArgumentListNode;
use Kirby\Toolkit\Query\AST\LiteralNode;
use Kirby\Toolkit\Query\AST\MemberAccessNode;
use Kirby\Toolkit\Query\AST\VariableNode;
use Kirby\Toolkit\Query\Parser;
use Kirby\Toolkit\Query\Tokenizer;

class ParserTest extends TestCase {
	function testMemberAccess() {
		$tokenizer = new Tokenizer('user.name');
		$ast = (new Parser($tokenizer))->parse();
		$this->assertEquals($ast,
			new MemberAccessNode(
				new VariableNode('user'), 'name'
			)
		);
	}

	function testSquentialMemberAccess() {
		$tokenizer = new Tokenizer('user.name("arg").age');
		$ast = (new Parser($tokenizer))->parse();
		$this->assertEquals($ast,
			new MemberAccessNode(
				new MemberAccessNode(
					new VariableNode('user'),
					'name',
					new ArgumentListNode([new LiteralNode("arg")])
				),
				'age'
			)
		);
	}
}
