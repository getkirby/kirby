<?php

namespace Kirby\Query\Parser;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\Parser\Token
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

	/**
	 * @covers ::is
	 */
	public function testis(): void
	{
		$token = new Token(
			type: TokenType::T_FLOAT,
			lexeme: '4.3',
		);

		$this->assertTrue($token->is(TokenType::T_FLOAT));
		$this->assertFalse($token->is(TokenType::T_INTEGER));
	}
}
