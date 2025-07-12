<?php

namespace Kirby\Query\Parser;

use Exception;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Tokenizer::class)]
class TokenizerTest extends TestCase
{
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
		return 'site.method?.([\'number\' => 3], null) ? (true ?: 4.1) : ("fox" ?? false)';
	}

	public static function tokenProvider(): array
	{
		return [
			[0, TokenType::T_IDENTIFIER, 'site'],
			[4, TokenType::T_DOT, '.'],
			[5, TokenType::T_IDENTIFIER, 'method'],
			[11, TokenType::T_NULLSAFE, '?.'],
			[13, TokenType::T_OPEN_PAREN, '('],
			[14, TokenType::T_OPEN_BRACKET, '['],
			[15, TokenType::T_STRING, '\'number\'', 'number'],
			[23, TokenType::T_WHITESPACE, ' '],
			[24, TokenType::T_ARROW, '=>'],
			[27, TokenType::T_INTEGER, '3', 3],
			[28, TokenType::T_CLOSE_BRACKET, ']'],
			[29, TokenType::T_COMMA, ','],
			[31, TokenType::T_NULL, 'null', null],
			[35, TokenType::T_CLOSE_PAREN, ')'],
			[37, TokenType::T_QUESTION_MARK, '?'],
			[39, TokenType::T_OPEN_PAREN, '('],
			[40, TokenType::T_TRUE, 'true', true],
			[45, TokenType::T_TERNARY_DEFAULT, '?:'],
			[48, TokenType::T_FLOAT, '4.1', 4.1],
			[51, TokenType::T_CLOSE_PAREN, ')'],
			[53, TokenType::T_COLON, ':'],
			[55, TokenType::T_OPEN_PAREN, '('],
			[56, TokenType::T_STRING, '"fox"', 'fox'],
			[62, TokenType::T_COALESCE, '??'],
			[65, TokenType::T_FALSE, 'false', false],
			[70, TokenType::T_CLOSE_PAREN, ')']
		];
	}

	#[DataProvider('tokenProvider')]
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

	public function testTokenInvalidCharacter(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid character in query: @');
		Tokenizer::token('a ?? @', 5);
	}

	public function testTokens(): void
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

	public function testTokensWithCoalesceWithZero(): void
	{
		$query = '(user.score ?? 0) > 100';
		$tokenizer = new Tokenizer($query);
		$tokens = iterator_to_array($tokenizer->tokens());

		$expected = [
			TokenType::T_OPEN_PAREN,
			TokenType::T_IDENTIFIER,    // user
			TokenType::T_DOT,
			TokenType::T_IDENTIFIER,    // score
			TokenType::T_COALESCE,      // ??
			TokenType::T_INTEGER,       // 0
			TokenType::T_CLOSE_PAREN,
			TokenType::T_GREATER_THAN,  // >
			TokenType::T_INTEGER,        // 100
			TokenType::T_EOF
		];

		$this->assertSame($expected, array_map(fn ($t) => $t->type, $tokens));

		$this->assertSame('user', $tokens[1]->lexeme);
		$this->assertSame('score', $tokens[3]->lexeme);
		$this->assertSame('??', $tokens[4]->lexeme);
		$this->assertSame('0', $tokens[5]->lexeme);
		$this->assertSame(0, $tokens[5]->literal);
		$this->assertSame('>', $tokens[7]->lexeme);
		$this->assertSame('100', $tokens[8]->lexeme);
		$this->assertSame(100, $tokens[8]->literal);
	}

	public static function comparisonTokenProvider(): array
	{
		return [
			['a == b',  2, TokenType::T_EQUAL, '=='],
			['a === b', 2, TokenType::T_IDENTICAL, '==='],
			['a != b',  2, TokenType::T_NOT_EQUAL, '!='],
			['a !== b', 2, TokenType::T_NOT_IDENTICAL, '!=='],
			['a < b',   2, TokenType::T_LESS_THAN, '<'],
			['a <= b',  2, TokenType::T_LESS_EQUAL, '<='],
			['a > b',   2, TokenType::T_GREATER_THAN, '>'],
			['a >= b',  2, TokenType::T_GREATER_EQUAL, '>='],
		];
	}

	#[DataProvider('comparisonTokenProvider')]
	public function testTokensWithComparisonOperators(
		string $query,
		int $offset,
		TokenType $expectedType,
		string $expectedLexeme
	): void {
		$token = Tokenizer::token($query, $offset);
		$this->assertSame($expectedType, $token->type);
		$this->assertSame($expectedLexeme, $token->lexeme);
	}

	public function testTokensWithLogicalOperatorsAndMemberAccess(): void
	{
		$query 	   = 'user.isAdmin && user.hasPermission';
		$tokenizer = new Tokenizer($query);
		$tokens    = iterator_to_array($tokenizer->tokens());

		$expected = [
			TokenType::T_IDENTIFIER,
			TokenType::T_DOT,
			TokenType::T_IDENTIFIER,
			TokenType::T_AND,
			TokenType::T_IDENTIFIER,
			TokenType::T_DOT,
			TokenType::T_IDENTIFIER,
			TokenType::T_EOF,
		];

		$this->assertSame($expected, array_map(fn ($t) => $t->type, $tokens));
	}

	public function testTokensWithComparisonOperatorsPrecedence(): void
	{
		// Test that longer operators are matched before shorter ones
		$query = 'a === b';
		$token = Tokenizer::token($query, 2);
		$this->assertSame(TokenType::T_IDENTICAL, $token->type);
		$this->assertSame('===', $token->lexeme);

		$query = 'a !== b';
		$token = Tokenizer::token($query, 2);
		$this->assertSame(TokenType::T_NOT_IDENTICAL, $token->type);
		$this->assertSame('!==', $token->lexeme);

		$query = 'a <= b';
		$token = Tokenizer::token($query, 2);
		$this->assertSame(TokenType::T_LESS_EQUAL, $token->type);
		$this->assertSame('<=', $token->lexeme);

		$query = 'a >= b';
		$token = Tokenizer::token($query, 2);
		$this->assertSame(TokenType::T_GREATER_EQUAL, $token->type);
		$this->assertSame('>=', $token->lexeme);
	}

	public function testTokensWithComparisonOperatorsSequence(): void
	{
		$query = 'a == b != c < d <= e > f >= g';
		$tokenizer = new Tokenizer($query);
		$tokens = iterator_to_array($tokenizer->tokens());

		$expected = [
			TokenType::T_IDENTIFIER,
			TokenType::T_EQUAL,
			TokenType::T_IDENTIFIER,
			TokenType::T_NOT_EQUAL,
			TokenType::T_IDENTIFIER,
			TokenType::T_LESS_THAN,
			TokenType::T_IDENTIFIER,
			TokenType::T_LESS_EQUAL,
			TokenType::T_IDENTIFIER,
			TokenType::T_GREATER_THAN,
			TokenType::T_IDENTIFIER,
			TokenType::T_GREATER_EQUAL,
			TokenType::T_IDENTIFIER,
			TokenType::T_EOF,
		];

		$this->assertSame($expected, array_map(fn ($t) => $t->type, $tokens));
	}

	public static function mathTokenProvider(): array
	{
		return [
			['a + b',  2, TokenType::T_PLUS, '+'],
			['a - b',  2, TokenType::T_MINUS, '-'],
			['a * b',  2, TokenType::T_MULTIPLY, '*'],
			['a / b',  2, TokenType::T_DIVIDE, '/'],
			['a % b',  2, TokenType::T_MODULO, '%'],
		];
	}

	#[DataProvider('mathTokenProvider')]
	public function testTokenWithMathOperators(
		string $query,
		int $offset,
		TokenType $expectedType,
		string $expectedLexeme
	): void {
		$token = Tokenizer::token($query, $offset);
		$this->assertSame($expectedType, $token->type);
		$this->assertSame($expectedLexeme, $token->lexeme);

	}
}
