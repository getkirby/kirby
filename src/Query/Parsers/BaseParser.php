<?php

namespace Kirby\Query\Parsers;

use Exception;
use Iterator;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class BaseParser
{
	protected Token $current;
	protected Token|null $previous;

	/**
	 * @var Iterator<Token>
	 */
	protected Iterator $tokens;

	public function __construct(string $query) {
		$tokenizer    = new Tokenizer($query);
		$this->tokens = $tokenizer->tokens();
		$first        = $this->tokens->current();

		if ($first === null) {
			throw new Exception('No tokens found');
		}

		$this->current = $first;
	}

	protected function consume(
		TokenType $type,
		string $error
	): Token {
		if ($this->check($type) === true) {
			return $this->advance();
		}

		throw new Exception($error);
	}

	protected function check(TokenType $type): bool
	{
		if ($this->isAtEnd() === true) {
			return false;
		}

		return $this->current->type === $type;
	}

	protected function advance(): Token|null
	{
		if ($this->isAtEnd() === false) {
			$this->previous = $this->current;
			$this->tokens->next();
			$this->current = $this->tokens->current();
		}

		return $this->previous;
	}

	protected function isAtEnd(): bool
	{
		return $this->current->type === TokenType::T_EOF;
	}


	protected function match(TokenType $type): Token|false
	{
		if ($this->check($type) === true) {
			return $this->advance();
		}

		return false;
	}

	protected function matchAny(array $types): Token|false
	{
		foreach ($types as $type) {
			if ($this->check($type) === true) {
				return $this->advance();
			}
		}

		return false;
	}
}
