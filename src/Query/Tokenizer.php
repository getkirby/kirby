<?php

namespace Kirby\Query;

use Exception;
use Generator;

class Tokenizer
{
	private int $length = 0;

	/**
	 * The more complex regexes are written here in nowdoc format so we don't need to double or triple escape backslashes (that becomes ridiculous rather fast).
	 */

	// Identifiers can contain letters, numbers, underscores and escaped dots. They can't start with a number.
	// to match an array key like "foo.bar" we write the query as `foo\.bar`, to match an array key like "foo\.bar" we write the query as `foo\\.bar`
	private const IDENTIFIER_REGEX = <<<'REGEX'
	(?:[\p{L}\p{N}_]|\\\.|\\\\)*
	REGEX;

	private const SINGLEQUOTE_STRING_REGEX = <<<'REGEX'
	'([^'\\]*(?:\\.[^'\\]*)*)'
	REGEX;

	private const DOUBLEQUOTE_STRING_REGEX = <<<'REGEX'
	"([^"\\]*(?:\\.[^"\\]*)*)"
	REGEX;

	public function __construct(
		private readonly string $source,
	) {
		$this->length = mb_strlen($source);
	}

	/**
	 * Tokenizes the source string and returns a generator of tokens.
	 * @return Generator<Token>
	 */
	public function tokenize(): Generator
	{
		$current = 0;

		while ($current < $this->length) {
			$token = static::scanToken($this->source, $current);

			// don't yield whitespace tokens (ignore them)
			if ($token->type !== TokenType::T_WHITESPACE) {
				yield $token;
			}

			$current += mb_strlen($token->lexeme);
		}

		yield new Token(TokenType::T_EOF, '', null);
	}

	/**
	 * Scans the source string for a token starting at the given position.
	 * @param string $source The source string
	 * @param int $current The current position in the source string
	 *
	 * @return Token The scanned token
	 * @throws Exception If an unexpected character is encountered
	 */
	protected static function scanToken(string $source, int $current): Token
	{
		$lex  = '';
		$char = $source[$current];

		return match(true) {
			// single character tokens
			$char === '.' => new Token(TokenType::T_DOT, '.'),
			$char === '(' => new Token(TokenType::T_OPEN_PAREN, '('),
			$char === ')' => new Token(TokenType::T_CLOSE_PAREN, ')'),
			$char === '[' => new Token(TokenType::T_OPEN_BRACKET, '['),
			$char === ']' => new Token(TokenType::T_CLOSE_BRACKET, ']'),
			$char === ',' => new Token(TokenType::T_COMMA, ','),
			$char === ':' => new Token(TokenType::T_COLON, ':'),

			// two character tokens
			static::match($source, $current, '\?\?', $lex)
				=> new Token(TokenType::T_COALESCE, $lex),
			static::match($source, $current, '\?\s*\.', $lex)
				=> new Token(TokenType::T_NULLSAFE, $lex),
			static::match($source, $current, '\?\s*:', $lex)
				=> new Token(TokenType::T_TERNARY_DEFAULT, $lex),
			static::match($source, $current, '=>', $lex)
				=> new Token(TokenType::T_ARROW, $lex),

			// make sure this check comes after the two above
			// that check for '?' in the beginning
			$char === '?' => new Token(TokenType::T_QUESTION_MARK, '?'),

			// multi character tokens
			static::match($source, $current, '\s+', $lex)
				=> new Token(TokenType::T_WHITESPACE, $lex),
			static::match($source, $current, 'true', $lex, true)
				=> new Token(TokenType::T_TRUE, $lex, true),
			static::match($source, $current, 'false', $lex, true)
				=> new Token(TokenType::T_FALSE, $lex, false),
			static::match($source, $current, 'null', $lex, true)
				=> new Token(TokenType::T_NULL, $lex, null),
			static::match($source, $current, static::DOUBLEQUOTE_STRING_REGEX, $lex)
				=> new Token(TokenType::T_STRING, $lex, stripcslashes(substr($lex, 1, -1))),
			static::match($source, $current, static::SINGLEQUOTE_STRING_REGEX, $lex)
				=> new Token(TokenType::T_STRING, $lex, stripcslashes(substr($lex, 1, -1))),
			static::match($source, $current, '\d+\b', $lex)
				=> new Token(TokenType::T_INTEGER, $lex, (int)$lex),
			static::match($source, $current, static::IDENTIFIER_REGEX, $lex)
				=> new Token(TokenType::T_IDENTIFIER, $lex),

			// unknown token
			default => throw new Exception("Unexpected character: {$source[$current]}"),
		};
	}

	/**
	 * Matches a regex pattern at the current position in the source string.
	 * The matched lexeme will be stored in the $lexeme variable.
	 *
	 * @param string $source The source string
	 * @param int $current The current position in the source string (used as offset for the regex)
	 * @param string $regex The regex pattern to match (without delimiters / flags)
	 * @param string $lexeme The matched lexeme will be stored in this variable
	 * @param bool $caseIgnore Whether to ignore case while matching
	 * @return bool Whether the regex pattern was matched
	 */
	protected static function match(
		string $source,
		int $current,
		string $regex,
		string &$lexeme,
		bool $caseIgnore = false
	): bool {
		$regex = '/\G' . $regex . '/u';

		if ($caseIgnore) {
			$regex .= 'i';
		}

		$matches = [];
		preg_match($regex, $source, $matches, 0, $current);

		if (empty($matches[0])) {
			return false;
		}

		$lexeme = $matches[0];
		return true;
	}
}
