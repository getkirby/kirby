<?php

namespace Kirby\Query;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\Tokenizer
 */
class TokenizerTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::tokens
	 */
	public function testTokens()
	{
		$string    = "user.likes(['(', ')']).drink";
		$tokenizer = new Tokenizer($string);
		$tokens    = $tokenizer->tokens();

		$token = $tokens->current();
		$this->assertSame(TokenType::T_IDENTIFIER, $token->type);
		$this->assertSame('user', $token->lexeme);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_DOT, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_IDENTIFIER, $token->type);
		$this->assertSame('likes', $token->lexeme);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_OPEN_PAREN, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_OPEN_BRACKET, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_STRING, $token->type);
		$this->assertSame("'('", $token->lexeme);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_COMMA, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_STRING, $token->type);
		$this->assertSame("')'", $token->lexeme);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_CLOSE_BRACKET, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_CLOSE_PAREN, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_DOT, $token->type);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_IDENTIFIER, $token->type);
		$this->assertSame('drink', $token->lexeme);

		$tokens->next();
		$token = $tokens->current();
		$this->assertSame(TokenType::T_EOF, $token->type);
	}

}
