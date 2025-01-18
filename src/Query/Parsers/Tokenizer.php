<?php

namespace Kirby\Query\Parsers;

use Exception;
use Generator;

/**
 * Parses a query string into its individual tokens
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Tokenizer
{
	private int $length = 0;

	/**
	 * The more complex regexes are written here in nowdoc format
	 * so we don't need to double or triple escape backslashes
	 * (that becomes ridiculous rather fast).
	 *
	 * Identifiers can contain letters, numbers, underscores and escaped dots.
	 * They can't start with a number.
	 *
	 * To match an array key like "foo.bar" we write the query as `foo\.bar`,
	 * to match an array key like "foo\.bar" we write the query as `foo\\.bar`
	 */
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
		// add delimiters and flags to the regex
		$regex = '/\G' . $regex . '/u';

		if ($caseInsensitive === true) {
			$regex .= 'i';
		}

		preg_match($regex, $query, $matches, 0, $offset);

		if (empty($matches[0]) === true) {
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
	 * @throws Exception If an unexpected character is encountered
	 */
	public static function token(string $query, int $current): Token
	{
		$char = $query[$current];

		// single character tokens:
		$token = match ($char) {
			'.'     => new Token(TokenType::T_DOT, '.'),
			'('     => new Token(TokenType::T_OPEN_PAREN, '('),
			')'     => new Token(TokenType::T_CLOSE_PAREN, ')'),
			'['     => new Token(TokenType::T_OPEN_BRACKET, '['),
			']'     => new Token(TokenType::T_CLOSE_BRACKET, ']'),
			','     => new Token(TokenType::T_COMMA, ','),
			':'     => new Token(TokenType::T_COLON, ':'),
			default => null
		};

		if ($token !== null) {
			return $token;
		}

		// two character tokens:
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

		// make sure this check comes after the two above
		// that check for '?' in the beginning
		if ($char === '?') {
			return new Token(TokenType::T_QUESTION_MARK, '?');
		}

		// multi character tokens:
		// whitespace
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
			return  new Token(
				TokenType::T_STRING,
				$lex,
				stripcslashes(substr($lex, 1, -1))
			);
		}

		// float
		if ($lex = static::match($query, $current, '-?\d+\.\d+\b')) {
			return new Token(TokenType::T_FLOAT, $lex, (float)$lex);
		}

		// int
		if ($lex = static::match($query, $current, '-?\d+\b')) {
			return new Token(TokenType::T_INTEGER, $lex, (int)$lex);
		}

		// identifier
		if ($lex = static::match($query, $current, static::IDENTIFIER_REGEX)) {
			return new Token(TokenType::T_IDENTIFIER, $lex);
		}

		// unknown token
		throw new Exception('Unexpected character: ' . $query[$current]);
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

			// don't yield whitespace tokens (ignore them)
			if ($token->type !== TokenType::T_WHITESPACE) {
				yield $token;
			}

			$current += mb_strlen($token->lexeme);
		}

		yield new Token(TokenType::T_EOF, '', null);
	}
}
