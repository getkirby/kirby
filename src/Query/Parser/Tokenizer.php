<?php

namespace Kirby\Query\Parser;

use Exception;
use Generator;

/**
 * Parses a query string into its individual tokens
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 */
class Tokenizer
{
	private int $length = 0;

	/**
	 * The more complex regexes are written here in nowdoc format
	 * so we don't need to double or triple escape backslashes
	 * (that becomes ridiculous rather fast).
	 *
	 * Identifiers can contain letters, numbers and underscores.
	 * They can't start with a number.
	 * For more complex identifier strings, subscript member access
	 * should be used. With `this` to access the global context.
	 */
	private const IDENTIFIER_REGEX = <<<'REGEX'
	(?:[\p{L}\p{N}_])*
	REGEX;

	private const SINGLEQUOTE_STRING_REGEX = <<<'REGEX'
	'([^'\\]*(?:\\.[^'\\]*)*)'
	REGEX;

	private const DOUBLEQUOTE_STRING_REGEX = <<<'REGEX'
	"([^"\\]*(?:\\.[^"\\]*)*)"
	REGEX;

	public function __construct(
		private readonly string $query,
	) {
		$this->length = mb_strlen($query);
	}

	/**
	 * Matches a regex pattern at the current position in the query string.
	 * The matched lexeme will be stored in the $lexeme variable.
	 *
	 * @param int $offset Current position in the query string
	 * @param string $regex Regex pattern without delimiters/flags
	 */
	public static function match(
		string $query,
		int $offset,
		string $regex,
		bool $caseInsensitive = false
	): string|null {
		// Add delimiters and flags to the regex
		$regex = '/\G' . $regex . '/u';

		if ($caseInsensitive === true) {
			$regex .= 'i';
		}

		if (preg_match($regex, $query, $matches, 0, $offset) !== 1) {
			return null;
		}

		return $matches[0];
	}

	/**
	 * Scans the source string for a next token
	 * starting from the given position
	 *
	 * @param int $current The current position in the source string
	 *
	 * @throws \Exception If an unexpected character is encountered
	 */
	public static function token(string $query, int $current): Token
	{
		$char = $query[$current];

		// Multi character tokens (check these first):
		// Whitespace
		if ($lex = static::match($query, $current, '\s+')) {
			return new Token(TokenType::T_WHITESPACE, $lex);
		}

		// true
		if ($lex = static::match($query, $current, 'true', true)) {
			return new Token(TokenType::T_TRUE, $lex, true);
		}

		// false
		if ($lex = static::match($query, $current, 'false', true)) {
			return new Token(TokenType::T_FALSE, $lex, false);
		}

		// null
		if ($lex = static::match($query, $current, 'null', true)) {
			return new Token(TokenType::T_NULL, $lex, null);
		}

		// "string"
		if ($lex = static::match($query, $current, static::DOUBLEQUOTE_STRING_REGEX)) {
			return new Token(
				TokenType::T_STRING,
				$lex,
				stripcslashes(substr($lex, 1, -1))
			);
		}

		// 'string'
		if ($lex = static::match($query, $current, static::SINGLEQUOTE_STRING_REGEX)) {
			return new Token(
				TokenType::T_STRING,
				$lex,
				stripcslashes(substr($lex, 1, -1))
			);
		}

		// float (check before single character tokens)
		$lex = static::match($query, $current, '-?\d+\.\d+\b');
		if ($lex !== null) {
			return new Token(TokenType::T_FLOAT, $lex, (float)$lex);
		}

		// int (check before single character tokens)
		$lex = static::match($query, $current, '-?\d+\b');
		if ($lex !== null) {
			return new Token(TokenType::T_INTEGER, $lex, (int)$lex);
		}

		// Two character tokens:
		// ??
		if ($lex = static::match($query, $current, '\?\?')) {
			return new Token(TokenType::T_COALESCE, $lex);
		}

		// ?.
		if ($lex = static::match($query, $current, '\?\s*\.')) {
			return new Token(TokenType::T_NULLSAFE, $lex);
		}

		// ?:
		if ($lex = static::match($query, $current, '\?\s*:')) {
			return new Token(TokenType::T_TERNARY_DEFAULT, $lex);
		}

		// =>
		if ($lex = static::match($query, $current, '=>')) {
			return new Token(TokenType::T_ARROW, $lex);
		}

		// Logical operators (check before comparison operators)
		if ($lex = static::match($query, $current, '&&|AND')) {
			return new Token(TokenType::T_AND, $lex);
		}

		if ($lex = static::match($query, $current, '\|\||OR')) {
			return new Token(TokenType::T_OR, $lex);
		}

		// Comparison operators (three characters first, then two, then one)
		// === (must come before ==)
		if ($lex = static::match($query, $current, '===')) {
			return new Token(TokenType::T_IDENTICAL, $lex);
		}

		// !== (must come before !=)
		if ($lex = static::match($query, $current, '!==')) {
			return new Token(TokenType::T_NOT_IDENTICAL, $lex);
		}

		// <= (must come before <)
		if ($lex = static::match($query, $current, '<=')) {
			return new Token(TokenType::T_LESS_EQUAL, $lex);
		}

		// >= (must come before >)
		if ($lex = static::match($query, $current, '>=')) {
			return new Token(TokenType::T_GREATER_EQUAL, $lex);
		}

		// ==
		if ($lex = static::match($query, $current, '==')) {
			return new Token(TokenType::T_EQUAL, $lex);
		}

		// !=
		if ($lex = static::match($query, $current, '!=')) {
			return new Token(TokenType::T_NOT_EQUAL, $lex);
		}

		// Single character tokens (check these last):
		$token = match ($char) {
			'.'     => new Token(TokenType::T_DOT, '.'),
			'('     => new Token(TokenType::T_OPEN_PAREN, '('),
			')'     => new Token(TokenType::T_CLOSE_PAREN, ')'),
			'['     => new Token(TokenType::T_OPEN_BRACKET, '['),
			']'     => new Token(TokenType::T_CLOSE_BRACKET, ']'),
			','     => new Token(TokenType::T_COMMA, ','),
			':'     => new Token(TokenType::T_COLON, ':'),
			'+'     => new Token(TokenType::T_PLUS, '+'),
			'-'     => new Token(TokenType::T_MINUS, '-'),
			'*'     => new Token(TokenType::T_MULTIPLY, '*'),
			'/'     => new Token(TokenType::T_DIVIDE, '/'),
			'%'     => new Token(TokenType::T_MODULO, '%'),
			'?'     => new Token(TokenType::T_QUESTION_MARK, '?'),
			'<'     => new Token(TokenType::T_LESS_THAN, '<'),
			'>'     => new Token(TokenType::T_GREATER_THAN, '>'),
			default => null
		};

		if ($token !== null) {
			return $token;
		}

		// Identifier
		if ($lex = static::match($query, $current, static::IDENTIFIER_REGEX)) {
			return new Token(TokenType::T_IDENTIFIER, $lex);
		}

		// Unknown token
		throw new Exception('Invalid character in query: ' . $query[$current]);
	}

	/**
	 * Tokenizes the query string and returns a generator of tokens.
	 * @return Generator<Token>
	 */
	public function tokens(): Generator
	{
		$current = 0;

		while ($current < $this->length) {
			$token = static::token($this->query, $current);

			// Don't yield whitespace tokens (ignore them)
			if ($token->type !== TokenType::T_WHITESPACE) {
				yield $token;
			}

			$current += mb_strlen($token->lexeme);
		}

		yield new Token(TokenType::T_EOF, '', null);
	}
}
