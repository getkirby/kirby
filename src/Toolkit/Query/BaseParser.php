<?php

namespace Kirby\Toolkit\Query;

use Iterator;

abstract class BaseParser {
	protected ?Token $previous;
	protected Token $current;

	/**
	 * @var Iterator<Token>
	 */
	protected Iterator $tokens;


	public function __construct(
		Tokenizer|Iterator $source,
	) {
		if($source instanceof Tokenizer) {
			$this->tokens = $source->tokenize();
		} else {
			$this->tokens = $source;
		}

		$first = $this->tokens->current();

		if ($first === null) {
			throw new \Exception('No tokens found.');
		}

		$this->current = $first;
	}

	protected function consume(TokenType $type, string $message): Token {
		if ($this->check($type)) {
			return $this->advance();
		}

		throw new \Exception($message);
	}

	protected function check(TokenType $type): bool {
		if ($this->isAtEnd()) {
			return false;
		}

		return $this->current->type === $type;
	}

	protected function advance(): ?Token {
		if (!$this->isAtEnd()) {
			$this->previous = $this->current;
			$this->tokens->next();
			$this->current = $this->tokens->current();
		}

		return $this->previous;
	}

	protected function isAtEnd(): bool {
		return $this->current->type === TokenType::EOF;
	}


	protected function match(TokenType $type): Token|false {
		if ($this->check($type)) {
			return $this->advance();
		}

		return false;
	}

	protected function matchAny(array $types): Token|false {
		foreach ($types as $type) {
			if ($this->check($type)) {
				return $this->advance();
			}
		}

		return false;
	}
}
