<?php

namespace Kirby\Query;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\Token
 */
class TokenTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testToken(): void
	{
		$token = new Token(
			type: $type = TokenType::T_FLOAT,
			lexeme: $lexeme = '4.3',
			literal: $literal = 4.3
		);

		$this->assertSame($type, $token->type);
		$this->assertSame($lexeme, $token->lexeme);
		$this->assertSame($literal, $token->literal);

		$token = new Token(
			type: $type = TokenType::T_IDENTIFIER,
			lexeme: $lexeme = 'page'
		);

		$this->assertSame($type, $token->type);
		$this->assertSame($lexeme, $token->lexeme);
		$this->assertNull($token->literal);
	}
}
