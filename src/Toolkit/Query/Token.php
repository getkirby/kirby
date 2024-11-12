<?php

namespace Kirby\Toolkit\Query;

class Token {
	public function __construct(
		public TokenType $type,
		public string $lexeme,
		public mixed $literal = null,
	) {}
}
