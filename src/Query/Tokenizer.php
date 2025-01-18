<?php

namespace Kirby\Query;

use Exception;
use Generator;


/**
 * Tokenizer
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 * 			  Nico Hoffmann <nico@getkirby.com>
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
		private readonly string $source,
	) {
		$this->length = mb_strlen($source);
	}

	/**
	 * Matches a regex pattern at the current position in the source string.
	 * The matched lexeme will be stored in the $lexeme variable.
	 *
	 * @param int $current Current position in the source string (used as offset for the regex)
	 * @param string $regex Regex pattern without delimiters/flags
	 * @param string $lexeme Matched lexeme will be stored in this variable
	 */
	protected static function match(
		string $source,
		int $current,
		string $regex,
		bool $caseIgnore = false
	): string|null {
		// add delimiters and flags to the regex
		$regex = '/\G' . $regex . '/u';

		if ($caseIgnore === true) {
			$regex .= 'i';
		}

		preg_match($regex, $source, $matches, 0, $current);

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
	protected static function token(string $source, int $current): Token
	{
		$lex  = '';
		$char = $source[$current];

		// single character tokens:
		$token = match($char) {
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
		if ($lex = static::match($source, $current, '\?\?')) {
			return new Token(TokenType::T_COALESCE, $lex);
		}

		// ?.
		if ($lex = static::match($source, $current, '\?\s*\.')) {
			return new Token(TokenType::T_NULLSAFE, $lex);
		}

		// ?:
		if ($lex = static::match($source, $current, '\?\s*:')) {
			return new Token(TokenType::T_TERNARY_DEFAULT, $lex);
		}

		// =>
		if ($lex = static::match($source, $current, '=>')) {
			return new Token(TokenType::T_ARROW, $lex);
		}

		// make sure this check comes after the two above
		// that check for '?' in the beginning
		if ($char === '?') {
			return new Token(TokenType::T_QUESTION_MARK, '?');
		}

		// multi character tokens:
		// whitespace
		if ($lex = static::match($source, $current, '\s+')) {
			return new Token(TokenType::T_WHITESPACE, $lex);
		}

		// true
		if ($lex = static::match($source, $current, 'true', true)) {
			return new Token(TokenType::T_TRUE, $lex, true);
		}

		// false
		if ($lex = static::match($source, $current, 'false', true)) {
			return new Token(TokenType::T_FALSE, $lex, false);
		}

		// null
		if ($lex = static::match($source, $current, 'null', true)) {
			return new Token(TokenType::T_NULL, $lex, null);
		}

		// "string"
		if ($lex = static::match($source, $current, static::DOUBLEQUOTE_STRING_REGEX)) {
			return new Token(
				TokenType::T_STRING,
				$lex,
				stripcslashes(substr($lex, 1, -1))
			);
		}

		// 'string'
		if ($lex = static::match($source, $current, static::SINGLEQUOTE_STRING_REGEX)) {
			return  new Token(
				TokenType::T_STRING,
				$lex,
				stripcslashes(substr($lex, 1, -1))
			);
		}

		// int
		if ($lex = static::match($source, $current, '\d+\b')) {
			return new Token(TokenType::T_INTEGER, $lex, (int)$lex);
		}

		// TODO: float?

		// identifier
		if ($lex = static::match($source, $current, static::IDENTIFIER_REGEX)) {
			return new Token(TokenType::T_IDENTIFIER, $lex);
		}

		// unknown token
		throw new Exception('Unexpected character: ' . $source[$current]);
	}

	/**
	 * Tokenizes the source string and returns a generator of tokens.
	 * @return Generator<Token>
	 */
	public function tokens(): Generator
	{
		$current = 0;

		while ($current < $this->length) {
			$token = static::token($this->source, $current);

			// don't yield whitespace tokens (ignore them)
			if ($token->type !== TokenType::T_WHITESPACE) {
				yield $token;
			}

			$current += mb_strlen($token->lexeme);
		}

		yield new Token(TokenType::T_EOF, '', null);
	}
}
