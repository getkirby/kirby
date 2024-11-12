<?php

namespace Kirby\Toolkit\Query;

use Generator;

class Tokenizer {
	private int $length = 0;

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
			if($t->type !== TokenType::WHITESPACE) {
				yield $t;
			}
			$current += mb_strlen($t->lexeme);
		}

		yield new Token(TokenType::EOF, '', null);
	}

	protected static function scanToken(string $source, int $current): Token {
		$l = '';
		$c = $source[$current];

		return match(true) {
			// single character tokens
			$c === '.' => new Token(TokenType::DOT, '.'),
			$c === '(' => new Token(TokenType::OPEN_PAREN, '('),
			$c === ')' => new Token(TokenType::CLOSE_PAREN, ')'),
			$c === '[' => new Token(TokenType::OPEN_BRACKET, '['),
			$c === ']' => new Token(TokenType::CLOSE_BRACKET, ']'),
			$c === ',' => new Token(TokenType::COMMA, ','),
			$c === ':' => new Token(TokenType::COLON, ':'),

			// two character tokens
			self::match($source, $current, '\\?\\?', $l) => new Token(TokenType::COALESCE, $l),
			self::match($source, $current, '\\?\\s*\\.', $l) => new Token(TokenType::NULLSAFE, $l),
			self::match($source, $current, '\\?\\s*:', $l) => new Token(TokenType::TERNARY_DEFAULT, $l),
			self::match($source, $current, '=>', $l) => new Token(TokenType::ARROW, $l),

			// make sure this check comes after the two above that check for '?' in the beginning
			$c === '?' => new Token(TokenType::QUESTION_MARK, '?'),

			// multi character tokens
			self::match($source, $current, '\\s+', $l) => new Token(TokenType::WHITESPACE, $l),
			self::match($source, $current, 'true', $l, true) => new Token(TokenType::TRUE, $l, true),
			self::match($source, $current, 'false', $l, true) => new Token(TokenType::FALSE, $l, false),
			self::match($source, $current, 'null', $l, true) => new Token(TokenType::NULL, $l, null),
			self::match($source, $current, '"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"', $l) => new Token(TokenType::STRING, $l, stripcslashes(substr($l, 1, -1))),
			self::match($source, $current, '\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'', $l) => new Token(TokenType::STRING, $l, stripcslashes(substr($l, 1, -1))),
			self::match($source, $current, '[0-9]+\\.[0-9]+', $l) => new Token(TokenType::NUMBER, $l, floatval($l)),
			self::match($source, $current, '[0-9]+', $l) => new Token(TokenType::NUMBER, $l, intval($l)),
			self::match($source, $current, '[a-zA-Z_][a-zA-Z0-9_]*', $l) => new Token(TokenType::IDENTIFIER, $l),


			// unknown token
			default => throw new \Exception("Unexpected character: {$source[$current]}"),
		};
	}

	/**
	 * Checks if a given regex matches the current position in the source. Returns the matched string or false. Advances the current position when a match is found.
	 * @param string $regex
	 * @return string|false
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
