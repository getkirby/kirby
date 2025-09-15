<?php

namespace Kirby\Query\Parser;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Token::class)]
class TokenTest extends TestCase
{
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

	public function testIs(): void
	{
		$token = new Token(
			type: TokenType::T_FLOAT,
			lexeme: '4.3',
		);

		$this->assertTrue($token->is(TokenType::T_FLOAT));
		$this->assertFalse($token->is(TokenType::T_INTEGER));
	}
}
