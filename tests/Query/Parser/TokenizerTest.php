<?php

namespace Kirby\Query\Parser;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Query\Parser\Tokenizer
 */
class TokenizerTest extends TestCase
{
	/**
	 * @covers ::match
	 */
	public function testMatch(): void
	{
		$string = 'Find ?? a TRUE';

		$this->assertNull(Tokenizer::match($string, 0, '\?\?'));
		$this->assertSame('??', Tokenizer::match($string, 5, '\?\?'));

		$this->assertNull(Tokenizer::match($string, 10, 'true'));
		$this->assertSame('TRUE', Tokenizer::match($string, 10, 'true', true));
	}

	public static function stringProvider(): string
	{
		return 'site?.([\'number\' => 3], null) ? (true ?: 4.1) : ("fox" ?? false)';
	}

	public static function tokenProvider(): array
	{
		return [
			[0, TokenType::T_IDENTIFIER, 'site'],
			[4, TokenType::T_NULLSAFE, '?.'],
			[6, TokenType::T_OPEN_PAREN, '('],
			[7, TokenType::T_OPEN_BRACKET, '['],
			[8, TokenType::T_STRING, '\'number\'', 'number'],
			[16, TokenType::T_WHITESPACE, ' '],
			[17, TokenType::T_ARROW, '=>'],
			[20, TokenType::T_INTEGER, '3', 3],
			[21, TokenType::T_CLOSE_BRACKET, ']'],
			[22, TokenType::T_COMMA, ','],
			[24, TokenType::T_NULL, 'null', null],
			[28, TokenType::T_CLOSE_PAREN, ')'],
			[30, TokenType::T_QUESTION_MARK, '?'],
			[32, TokenType::T_OPEN_PAREN, '('],
			[33, TokenType::T_TRUE, 'true', true],
			[38, TokenType::T_TERNARY_DEFAULT, '?:'],
			[41, TokenType::T_FLOAT, '4.1', 4.1],
			[44, TokenType::T_CLOSE_PAREN, ')'],
			[46, TokenType::T_COLON, ':'],
			[48, TokenType::T_OPEN_PAREN, '('],
			[49, TokenType::T_STRING, '"fox"', 'fox'],
			[55, TokenType::T_COALESCE, '??'],
			[58, TokenType::T_FALSE, 'false', false],
			[63, TokenType::T_CLOSE_PAREN, ')']
		];
	}

	/**
	 * @covers ::token
	 * @dataProvider tokenProvider
	 */
	public function testToken(
		int $offset,
		TokenType $type,
		string $lexeme,
		mixed $literal = null
	): void {
		$string = static::stringProvider();
		$token  = Tokenizer::token($string, $offset);
		$this->assertSame($type, $token->type);
		$this->assertSame($lexeme, $token->lexeme);
		$this->assertSame($literal, $token->literal);
	}

	/**
	 * @covers ::__construct
	 * @covers ::tokens
	 */
	public function testTokens()
	{
		$string    = static::stringProvider();
		$tokenizer = new Tokenizer($string);
		$tokens    = $tokenizer->tokens();

		foreach (static::tokenProvider() as $expected) {
			if ($expected[1] === TokenType::T_WHITESPACE) {
				continue;
			}

			$token = $tokens->current();
			$this->assertSame($expected[1], $token->type);
			$this->assertSame($expected[2], $token->lexeme);
			$this->assertSame($expected[3] ?? null, $token->literal);
			$tokens->next();
		}

		$token = $tokens->current();
		$this->assertSame(TokenType::T_EOF, $token->type);
	}
}
