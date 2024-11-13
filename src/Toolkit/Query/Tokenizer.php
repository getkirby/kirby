<?php

namespace Kirby\Toolkit\Query;

use Exception;
use Generator;

class Tokenizer {
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
	public function tokenize(): Generator {
		$current = 0;

		while ($current < $this->length) {
			$t = self::scanToken($this->source, $current);
			// don't yield whitespace tokens (ignore them)
			if($t->type !== TokenType::T_WHITESPACE) {
				yield $t;
			}
			$current += mb_strlen($t->lexeme);
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
	protected static function scanToken(string $source, int $current): Token {
		$l = '';
		$c = $source[$current];

		return match(true) {
			// single character tokens
			$c === '.' => new Token(TokenType::T_DOT, '.'),
			$c === '(' => new Token(TokenType::T_OPEN_PAREN, '('),
			$c === ')' => new Token(TokenType::T_CLOSE_PAREN, ')'),
			$c === '[' => new Token(TokenType::T_OPEN_BRACKET, '['),
			$c === ']' => new Token(TokenType::T_CLOSE_BRACKET, ']'),
			$c === ',' => new Token(TokenType::T_COMMA, ','),
			$c === ':' => new Token(TokenType::T_COLON, ':'),

			// two character tokens
			self::match($source, $current, '\?\?', $l) => new Token(TokenType::T_COALESCE, $l),
			self::match($source, $current, '\?\s*\.', $l) => new Token(TokenType::T_NULLSAFE, $l),
			self::match($source, $current, '\?\s*:', $l) => new Token(TokenType::T_TERNARY_DEFAULT, $l),
			self::match($source, $current, '=>', $l) => new Token(TokenType::T_ARROW, $l),

			// make sure this check comes after the two above that check for '?' in the beginning
			$c === '?' => new Token(TokenType::T_QUESTION_MARK, '?'),

			// multi character tokens
			self::match($source, $current, '\s+', $l) => new Token(TokenType::T_WHITESPACE, $l),
			self::match($source, $current, 'true', $l, true) => new Token(TokenType::T_TRUE, $l, true),
			self::match($source, $current, 'false', $l, true) => new Token(TokenType::T_FALSE, $l, false),
			self::match($source, $current, 'null', $l, true) => new Token(TokenType::T_NULL, $l, null),
			self::match($source, $current, self::DOUBLEQUOTE_STRING_REGEX, $l) => new Token(TokenType::T_STRING, $l, stripcslashes(substr($l, 1, -1))),
			self::match($source, $current, self::SINGLEQUOTE_STRING_REGEX, $l) => new Token(TokenType::T_STRING, $l, stripcslashes(substr($l, 1, -1))),
			self::match($source, $current, '\d+\b', $l) => new Token(TokenType::T_INTEGER, $l, intval($l)),
			self::match($source, $current, self::IDENTIFIER_REGEX, $l) => new Token(TokenType::T_IDENTIFIER, $l),

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
	protected static function match(string $source, int $current, string $regex, string &$lexeme, bool $caseIgnore = false): bool {
		$regex = '/\G' . $regex . '/u';
		if($caseIgnore) {
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
